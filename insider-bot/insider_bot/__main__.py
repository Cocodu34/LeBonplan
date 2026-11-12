"""Point d'entrée : python -m insider_bot {run,alerts,test-notify}."""

import argparse
import json
import logging
import sys
from datetime import datetime

from .api import FixtureClient, PolymarketClient
from .bot import InsiderBot
from .config import load_config
from .notify import broadcast, build_notifiers
from .store import Store


def main(argv=None):
    parser = argparse.ArgumentParser(prog="insider_bot", description="Détecteur de paris d'initiés sur Polymarket.")
    parser.add_argument("--config", help="fichier JSON de configuration")
    parser.add_argument("--db", help="chemin de la base SQLite")
    parser.add_argument("-v", "--verbose", action="store_true")
    sub = parser.add_subparsers(dest="command", required=True)

    run = sub.add_parser("run", help="surveille Polymarket en continu")
    run.add_argument("--once", action="store_true", help="un seul cycle puis quitte")
    run.add_argument("--fixtures", help="rejoue des données locales (démo hors-ligne)")

    alerts = sub.add_parser("alerts", help="affiche les dernières alertes enregistrées")
    alerts.add_argument("-n", type=int, default=20)

    sub.add_parser("test-notify", help="envoie une fausse alerte pour tester Telegram/Discord")

    args = parser.parse_args(argv)
    logging.basicConfig(
        level=logging.DEBUG if args.verbose else logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
    )
    cfg = load_config(args.config)
    if args.db:
        cfg["db_path"] = args.db

    if args.command == "test-notify":
        broadcast(build_notifiers(cfg), {
            "score": 99, "title": "TEST — alerte factice", "condition_id": "0xtest", "side": "BUY",
            "outcome": "Yes", "price": 0.12, "usd": 25000, "wallet": "0x0000000000000000000000000000000000000000",
            "signals": [{"name": "test", "points": 99, "detail": "message de test"}],
            "market_url": "https://polymarket.com", "profile_url": "https://polymarket.com",
        })
        return 0

    store = Store(cfg["db_path"])
    try:
        if args.command == "alerts":
            for a in store.list_alerts(args.n):
                when = datetime.fromtimestamp(a["ts"]).strftime("%Y-%m-%d %H:%M")
                print(f"{when}  score {a['score']:>2}  {a['usd']:>10,.0f} $  @{a['price']:.2f}  "
                      f"{a['outcome']:<6} {a['title']}  ({a['wallet']})")
            return 0

        client = FixtureClient(args.fixtures) if args.fixtures else PolymarketClient()
        bot = InsiderBot(client, store, build_notifiers(cfg), cfg)
        if args.once or args.fixtures:
            found = bot.poll_once()
            logging.info("%d alerte(s).", len(found))
        else:
            bot.run()
    except KeyboardInterrupt:
        pass
    finally:
        store.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
