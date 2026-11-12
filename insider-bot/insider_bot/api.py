"""Clients de données : API publiques Polymarket (sans clé) et un client "fixtures" hors-ligne."""

import json
import os
import time
import urllib.error
import urllib.parse
import urllib.request

DATA_API = "https://data-api.polymarket.com"
GAMMA_API = "https://gamma-api.polymarket.com"


class ApiError(RuntimeError):
    pass


class PolymarketClient:
    def __init__(self, timeout=15, retries=3):
        self.timeout = timeout
        self.retries = retries

    def now(self):
        return time.time()

    def _get(self, base, path, params):
        url = f"{base}{path}?{urllib.parse.urlencode(params)}"
        req = urllib.request.Request(
            url, headers={"User-Agent": "insider-bot/1.0", "Accept": "application/json"}
        )
        last_error = None
        for attempt in range(self.retries):
            try:
                with urllib.request.urlopen(req, timeout=self.timeout) as resp:
                    return json.load(resp)
            except urllib.error.HTTPError as exc:
                last_error = exc
                if exc.code != 429 and exc.code < 500:
                    raise ApiError(f"{url} -> HTTP {exc.code}") from exc
            except (urllib.error.URLError, TimeoutError, json.JSONDecodeError) as exc:
                last_error = exc
            time.sleep(2 ** attempt)
        raise ApiError(f"{url} -> échec après {self.retries} essais : {last_error}")

    def recent_trades(self, limit=500, min_usd=0):
        """Derniers trades "taker" de tout Polymarket, du plus récent au plus ancien."""
        params = {"limit": limit, "takerOnly": "true"}
        if min_usd:
            params.update(filterType="CASH", filterAmount=min_usd)
        return self._get(DATA_API, "/trades", params) or []

    def wallet_activity(self, wallet, limit=500):
        """Historique d'un wallet, du plus ancien au plus récent (pour connaître son âge)."""
        params = {"user": wallet, "limit": limit, "sortBy": "TIMESTAMP", "sortDirection": "ASC"}
        return self._get(DATA_API, "/activity", params) or []

    def market(self, condition_id):
        markets = self._get(GAMMA_API, "/markets", {"condition_ids": condition_id}) or []
        return markets[0] if markets else None


class FixtureClient:
    """Rejoue des données enregistrées (dossier fixtures/) : démo et tests sans réseau."""

    def __init__(self, directory):
        self.dir = directory
        with open(os.path.join(directory, "trades.json"), encoding="utf-8") as fh:
            data = json.load(fh)
        self._now = data["now"]
        self._trades = data["trades"]
        with open(os.path.join(directory, "markets.json"), encoding="utf-8") as fh:
            self._markets = json.load(fh)

    def now(self):
        return self._now

    def recent_trades(self, limit=500, min_usd=0):
        return self._trades[:limit]

    def wallet_activity(self, wallet, limit=500):
        path = os.path.join(self.dir, "activity", f"{wallet.lower()}.json")
        if not os.path.exists(path):
            return []
        with open(path, encoding="utf-8") as fh:
            return json.load(fh)[:limit]

    def market(self, condition_id):
        return self._markets.get(condition_id)
