"""Boucle principale : récupère les trades, profile les wallets, score et alerte."""

import logging
import time

from .notify import broadcast
from .scoring import DAY, build_profile, is_fresh, score_trade

log = logging.getLogger(__name__)

ACTIVITY_LIMIT = 500
MARKET_CACHE_SECONDS = 3600


class InsiderBot:
    def __init__(self, client, store, notifiers, cfg):
        self.client = client
        self.store = store
        self.notifiers = notifiers
        self.cfg = cfg
        self._markets = {}

    def _profile(self, wallet, now):
        min_fetched = now - self.cfg["wallet_cache_minutes"] * 60
        profile = self.store.get_wallet(wallet, min_fetched)
        if profile is None:
            activity = self.client.wallet_activity(wallet, limit=ACTIVITY_LIMIT)
            profile = build_profile(activity, ACTIVITY_LIMIT)
            self.store.put_wallet(wallet, profile, now)
        return profile

    def _market(self, condition_id, now):
        cached = self._markets.get(condition_id)
        if cached and now - cached[0] < MARKET_CACHE_SECONDS:
            return cached[1]
        try:
            market = self.client.market(condition_id)
        except Exception as exc:
            log.warning("Marché %s introuvable : %s", condition_id, exc)
            market = None
        self._markets[condition_id] = (now, market)
        return market

    def _excluded(self, trade):
        slug = f"{trade.get('slug', '')} {trade.get('eventSlug', '')}".lower()
        return any(k in slug for k in self.cfg["exclude_slug_keywords"])

    def poll_once(self):
        cfg = self.cfg
        now = self.client.now()
        trades = self.client.recent_trades(limit=cfg["trades_per_poll"], min_usd=cfg["min_trade_usd"])
        alerts = []
        for trade in sorted(trades, key=lambda t: float(t.get("timestamp", 0))):
            wallet = (trade.get("proxyWallet") or "").lower()
            if not wallet:
                continue
            key = f"{trade.get('transactionHash')}:{trade.get('asset')}:{wallet}"
            if not self.store.mark_seen(key, now):
                continue
            usd = float(trade["size"]) * float(trade["price"])
            if usd < cfg["min_trade_usd"] or self._excluded(trade):
                continue
            # Acheter à 0.95+ = encaisser une issue quasi certaine ("farming") : aucune info privilégiée.
            if trade.get("side", "BUY").upper() == "BUY" and float(trade["price"]) >= cfg["near_certain_price"]:
                continue

            alert = self._analyse(trade, wallet, usd, now)
            if alert:
                alerts.append(alert)

        self.store.prune(now - 2 * DAY)
        return alerts

    def _analyse(self, trade, wallet, usd, now):
        cfg = self.cfg
        condition_id = trade["conditionId"]
        outcome = str(trade.get("outcome") or trade.get("outcomeIndex"))
        ts = float(trade.get("timestamp", now))

        profile = self._profile(wallet, now)
        if is_fresh(profile, now, cfg) and trade.get("side", "BUY").upper() == "BUY":
            self.store.record_fresh_buy(wallet, condition_id, outcome, ts)
        cluster = self.store.cluster_size(condition_id, outcome, now - cfg["cluster_window_hours"] * 3600)

        market = self._market(condition_id, now)
        score, signals = score_trade(trade, profile, market, cluster, now, cfg)
        log.debug("%s %s %.0f$ score=%d", wallet, trade.get("slug"), usd, score)
        if score < cfg["alert_score"]:
            return None
        if self.store.recently_alerted(wallet, condition_id, outcome, now - cfg["realert_cooldown_hours"] * 3600):
            return None

        event_slug = trade.get("eventSlug") or trade.get("slug") or ""
        alert = {
            "ts": now,
            "wallet": wallet,
            "condition_id": condition_id,
            "outcome": outcome,
            "side": trade.get("side", "BUY").upper(),
            "title": trade.get("title") or (market or {}).get("question"),
            "price": float(trade["price"]),
            "usd": usd,
            "score": score,
            "signals": signals,
            "market_url": f"https://polymarket.com/event/{event_slug}",
            "profile_url": f"https://polymarket.com/profile/{wallet}",
        }
        self.store.save_alert(alert)
        broadcast(self.notifiers, alert)
        return alert

    def run(self):
        log.info("Insider bot démarré (seuil %d, min %s $).", self.cfg["alert_score"], self.cfg["min_trade_usd"])
        while True:
            try:
                alerts = self.poll_once()
                log.info("Cycle terminé : %d alerte(s).", len(alerts))
            except Exception as exc:
                log.error("Erreur pendant le cycle : %s", exc)
            time.sleep(self.cfg["poll_interval_seconds"])
