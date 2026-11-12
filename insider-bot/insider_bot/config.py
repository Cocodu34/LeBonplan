"""Configuration : valeurs par défaut, surchargées par un fichier JSON puis par l'environnement."""

import json
import os

DEFAULTS = {
    # --- Collecte ---
    "poll_interval_seconds": 30,
    "trades_per_poll": 500,
    # Montant minimum (en USDC) d'un trade pour être analysé.
    "min_trade_usd": 5000,
    # Mots-clés de slug de marché ignorés (marchés crypto "up or down" ultra-courts = bruit).
    "exclude_slug_keywords": ["updown", "up-or-down"],

    # --- Profil de wallet ---
    "fresh_wallet_days": 7,          # wallet "tout neuf"
    "young_wallet_days": 30,         # wallet "jeune"
    "few_markets_max": 3,            # a joué sur très peu de marchés
    "experienced_wallet_trades": 200,  # au-delà : trader régulier, pas un initié ponctuel
    "wallet_cache_minutes": 10,

    # --- Signaux ---
    "big_trade_multiplier": 4,       # >= min_trade_usd * 4 => gros trade
    "longshot_price": 0.25,          # achat d'une issue jugée improbable
    "unlikely_price": 0.40,
    "near_certain_price": 0.95,      # achats à 0.95+ = "farming", pas de l'initié
    "ends_soon_hours": 72,
    "cluster_window_hours": 24,

    # --- Alertes ---
    "alert_score": 7,
    "realert_cooldown_hours": 24,

    # --- Notifications (laisser vide pour désactiver) ---
    "telegram_bot_token": "",
    "telegram_chat_id": "",
    "discord_webhook_url": "",

    "db_path": "insider_bot.sqlite3",
}

ENV_OVERRIDES = {
    "TELEGRAM_BOT_TOKEN": "telegram_bot_token",
    "TELEGRAM_CHAT_ID": "telegram_chat_id",
    "DISCORD_WEBHOOK_URL": "discord_webhook_url",
    "INSIDER_MIN_TRADE_USD": "min_trade_usd",
    "INSIDER_ALERT_SCORE": "alert_score",
    "INSIDER_DB_PATH": "db_path",
}


def load_config(path=None):
    cfg = dict(DEFAULTS)
    if path:
        with open(path, encoding="utf-8") as fh:
            user = json.load(fh)
        unknown = set(user) - set(DEFAULTS)
        if unknown:
            raise ValueError(f"Clés inconnues dans {path} : {', '.join(sorted(unknown))}")
        cfg.update(user)
    for env, key in ENV_OVERRIDES.items():
        value = os.environ.get(env)
        if value:
            cfg[key] = type(DEFAULTS[key])(value) if not isinstance(DEFAULTS[key], str) else value
    return cfg
