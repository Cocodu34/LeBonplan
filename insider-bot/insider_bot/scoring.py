"""Profilage des wallets et score de suspicion d'un trade.

Chaque signal ajoute (ou retire) des points. Un trade dont le score atteint
`alert_score` déclenche une alerte. Les pénalités servent à filtrer les faux
positifs classiques : traders réguliers, ventes. (Les achats à 0.95+ sont
écartés en amont par le bot.)
"""

from dataclasses import asdict, dataclass
from datetime import datetime

DAY = 86400


@dataclass
class Signal:
    name: str
    points: int
    detail: str


def build_profile(activity, limit):
    """Résume l'historique (/activity, trié du plus ancien au plus récent) d'un wallet."""
    trades = [a for a in activity if a.get("type", "TRADE") == "TRADE"]
    timestamps = [float(a["timestamp"]) for a in activity if a.get("timestamp")]
    return {
        "first_seen": min(timestamps) if timestamps else None,
        "n_trades": len(trades),
        "n_markets": len({a.get("conditionId") for a in trades if a.get("conditionId")}),
        "total_volume": sum(float(a.get("usdcSize") or 0) for a in trades),
        # Historique tronqué => le wallet a au moins `limit` activités : c'est un habitué.
        "truncated": len(activity) >= limit,
    }


def wallet_age_days(profile, now):
    if not profile.get("first_seen"):
        return 0.0  # aucune activité connue : traité comme tout neuf
    return max(0.0, (now - profile["first_seen"]) / DAY)


def is_fresh(profile, now, cfg):
    return wallet_age_days(profile, now) < cfg["fresh_wallet_days"] and not profile.get("truncated")


def hours_until_end(market, now):
    end = (market or {}).get("endDate")
    if not end:
        return None
    try:
        end_ts = datetime.fromisoformat(end.replace("Z", "+00:00")).timestamp()
    except ValueError:
        return None
    return (end_ts - now) / 3600


def score_trade(trade, profile, market, cluster, now, cfg):
    usd = float(trade["size"]) * float(trade["price"])
    price = float(trade["price"])
    side = trade.get("side", "BUY").upper()
    signals = []

    def add(name, points, detail):
        signals.append(Signal(name, points, detail))

    # 1. Taille du pari
    if usd >= cfg["min_trade_usd"] * cfg["big_trade_multiplier"]:
        add("gros_pari", 2, f"gros pari : {usd:,.0f} $")
    else:
        add("pari_important", 1, f"pari important : {usd:,.0f} $")

    # 2. Âge du wallet
    age = wallet_age_days(profile, now)
    if profile.get("truncated") or profile["n_trades"] >= cfg["experienced_wallet_trades"]:
        add("trader_regulier", -3, f"{profile['n_trades']}+ trades : habitué, pas un initié ponctuel")
    elif age < cfg["fresh_wallet_days"]:
        add("wallet_neuf", 3, f"wallet actif depuis {age:.1f} j")
    elif age < cfg["young_wallet_days"]:
        add("wallet_jeune", 1, f"wallet actif depuis {age:.0f} j")

    # 3. Peu de marchés joués / pari concentré
    if not profile.get("truncated") and profile["n_markets"] <= cfg["few_markets_max"]:
        add("peu_de_marches", 2, f"n'a joué que sur {profile['n_markets']} marché(s)")
    if profile["total_volume"] > 0 and usd / profile["total_volume"] >= 0.5:
        add("pari_concentre", 1, f"{usd / profile['total_volume']:.0%} de son volume total")

    # 4. Prix : pari sur une issue improbable (asymétrique) vs quasi-certitude
    if side == "BUY":
        if price <= cfg["longshot_price"]:
            add("issue_improbable", 2, f"achat à {price:.2f} (le marché n'y croit qu'à {price:.0%})")
        elif price <= cfg["unlikely_price"]:
            add("issue_peu_probable", 1, f"achat à {price:.2f} (issue peu probable)")
    else:
        add("vente", -1, "vente : moins informatif qu'un achat")

    # 5. Échéance proche
    hours = hours_until_end(market, now)
    if hours is not None and 0 < hours <= cfg["ends_soon_hours"]:
        add("echeance_proche", 2, f"le marché se termine dans {hours:.0f} h")

    # 6. Cluster : plusieurs wallets neufs sur la même issue
    if cluster >= 3:
        add("cluster", 3, f"{cluster} wallets neufs sur la même issue en {cfg['cluster_window_hours']} h")
    elif cluster == 2:
        add("cluster_leger", 1, "2 wallets neufs sur la même issue")

    score = sum(s.points for s in signals)
    return score, [asdict(s) for s in signals]
