import os
import tempfile
import unittest

from insider_bot.api import FixtureClient
from insider_bot.bot import InsiderBot
from insider_bot.config import DEFAULTS
from insider_bot.scoring import DAY, build_profile, score_trade
from insider_bot.store import Store

FIXTURES = os.path.join(os.path.dirname(__file__), "..", "fixtures")
NOW = 1_762_950_000


class Collector:
    def __init__(self):
        self.alerts = []

    def send(self, alert):
        self.alerts.append(alert)


def trade(price=0.10, usd=10_000, side="BUY"):
    return {"proxyWallet": "0xabc", "conditionId": "0xc", "price": price, "size": usd / price,
            "side": side, "outcome": "Yes", "timestamp": NOW}


def fresh_profile(**kw):
    p = {"first_seen": NOW - DAY, "n_trades": 1, "n_markets": 1, "total_volume": 10_000, "truncated": False}
    p.update(kw)
    return p


class ScoringTest(unittest.TestCase):
    def test_fresh_wallet_longshot_scores_high(self):
        score, signals = score_trade(trade(), fresh_profile(), None, 1, NOW, DEFAULTS)
        names = {s["name"] for s in signals}
        self.assertIn("wallet_neuf", names)
        self.assertIn("issue_improbable", names)
        self.assertGreaterEqual(score, DEFAULTS["alert_score"])

    def test_experienced_trader_is_penalised(self):
        pro = fresh_profile(first_seen=NOW - 400 * DAY, n_trades=500, n_markets=300,
                            total_volume=2_000_000, truncated=True)
        score, _ = score_trade(trade(price=0.45), pro, None, 0, NOW, DEFAULTS)
        self.assertLess(score, DEFAULTS["alert_score"])

    def test_sell_is_penalised(self):
        buy, _ = score_trade(trade(), fresh_profile(), None, 0, NOW, DEFAULTS)
        sell, _ = score_trade(trade(side="SELL"), fresh_profile(), None, 0, NOW, DEFAULTS)
        self.assertLess(sell, buy)

    def test_cluster_and_deadline_add_points(self):
        base, _ = score_trade(trade(price=0.5), fresh_profile(), None, 0, NOW, DEFAULTS)
        market = {"endDate": "2025-11-13T12:00:00Z"}  # ~ 22 h après NOW
        boosted, signals = score_trade(trade(price=0.5), fresh_profile(), market, 3, NOW, DEFAULTS)
        self.assertEqual(boosted - base, 3 + 2)
        self.assertIn("echeance_proche", {s["name"] for s in signals})

    def test_build_profile(self):
        activity = [
            {"type": "TRADE", "timestamp": NOW - 3 * DAY, "conditionId": "a", "usdcSize": "100"},
            {"type": "REDEEM", "timestamp": NOW - 2 * DAY, "conditionId": "a"},
            {"type": "TRADE", "timestamp": NOW - DAY, "conditionId": "b", "usdcSize": 50},
        ]
        p = build_profile(activity, limit=500)
        self.assertEqual((p["n_trades"], p["n_markets"], p["total_volume"]), (2, 2, 150.0))
        self.assertEqual(p["first_seen"], NOW - 3 * DAY)
        self.assertFalse(p["truncated"])


class BotTest(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.store = Store(os.path.join(self.tmp.name, "t.db"))
        self.out = Collector()
        self.bot = InsiderBot(FixtureClient(FIXTURES), self.store, [self.out], dict(DEFAULTS))

    def tearDown(self):
        self.store.close()
        self.tmp.cleanup()

    def wallets(self):
        return {a["wallet"][-2:] for a in self.out.alerts}

    def test_fixture_scenarios(self):
        self.bot.poll_once()
        # 01-03 : cluster de wallets neufs ; 0e : wallet jeune, gros pari concentré.
        # 0a (habitué), 0b (farming 0.97), 0c (marché up/down), 0d (petit trade) : pas d'alerte.
        self.assertEqual(self.wallets(), {"01", "02", "03", "0e"})
        third = next(a for a in self.out.alerts if a["wallet"].endswith("03"))
        self.assertIn("cluster", {s["name"] for s in third["signals"]})

    def test_second_poll_does_not_realert(self):
        self.bot.poll_once()
        first = len(self.out.alerts)
        self.assertEqual(self.bot.poll_once(), [])
        self.assertEqual(len(self.out.alerts), first)
        self.assertEqual(len(self.store.list_alerts(100)), first)

    def test_failing_notifier_does_not_crash(self):
        class Broken:
            def send(self, alert):
                raise OSError("réseau coupé")
        self.bot.notifiers = [Broken(), self.out]
        self.bot.poll_once()
        self.assertTrue(self.out.alerts)


if __name__ == "__main__":
    unittest.main()
