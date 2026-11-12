"""Persistance SQLite : déduplication, cache des wallets, détection de clusters, historique d'alertes."""

import json
import sqlite3

SCHEMA = """
CREATE TABLE IF NOT EXISTS seen_trades (key TEXT PRIMARY KEY, ts REAL NOT NULL);
CREATE TABLE IF NOT EXISTS wallets (wallet TEXT PRIMARY KEY, profile TEXT NOT NULL, fetched_at REAL NOT NULL);
CREATE TABLE IF NOT EXISTS fresh_buys (
    wallet TEXT NOT NULL, condition_id TEXT NOT NULL, outcome TEXT NOT NULL, ts REAL NOT NULL,
    PRIMARY KEY (wallet, condition_id, outcome)
);
CREATE TABLE IF NOT EXISTS alerts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ts REAL NOT NULL, wallet TEXT NOT NULL, condition_id TEXT NOT NULL, outcome TEXT NOT NULL,
    title TEXT, price REAL, usd REAL, score INTEGER, signals TEXT
);
CREATE INDEX IF NOT EXISTS idx_alerts_key ON alerts (wallet, condition_id, outcome, ts);
"""


class Store:
    def __init__(self, path):
        self.db = sqlite3.connect(path)
        self.db.row_factory = sqlite3.Row
        self.db.executescript(SCHEMA)

    def close(self):
        self.db.close()

    # --- Déduplication ---
    def mark_seen(self, key, ts):
        """Renvoie True si le trade est nouveau."""
        cur = self.db.execute("INSERT OR IGNORE INTO seen_trades (key, ts) VALUES (?, ?)", (key, ts))
        self.db.commit()
        return cur.rowcount == 1

    def prune(self, older_than):
        self.db.execute("DELETE FROM seen_trades WHERE ts < ?", (older_than,))
        self.db.execute("DELETE FROM fresh_buys WHERE ts < ?", (older_than,))
        self.db.commit()

    # --- Cache des profils ---
    def get_wallet(self, wallet, min_fetched_at):
        row = self.db.execute(
            "SELECT profile FROM wallets WHERE wallet = ? AND fetched_at >= ?", (wallet, min_fetched_at)
        ).fetchone()
        return json.loads(row["profile"]) if row else None

    def put_wallet(self, wallet, profile, fetched_at):
        self.db.execute(
            "INSERT OR REPLACE INTO wallets (wallet, profile, fetched_at) VALUES (?, ?, ?)",
            (wallet, json.dumps(profile), fetched_at),
        )
        self.db.commit()

    # --- Clusters de wallets neufs ---
    def record_fresh_buy(self, wallet, condition_id, outcome, ts):
        self.db.execute(
            "INSERT OR REPLACE INTO fresh_buys (wallet, condition_id, outcome, ts) VALUES (?, ?, ?, ?)",
            (wallet, condition_id, outcome, ts),
        )
        self.db.commit()

    def cluster_size(self, condition_id, outcome, since):
        row = self.db.execute(
            "SELECT COUNT(DISTINCT wallet) AS n FROM fresh_buys WHERE condition_id = ? AND outcome = ? AND ts >= ?",
            (condition_id, outcome, since),
        ).fetchone()
        return row["n"]

    # --- Alertes ---
    def recently_alerted(self, wallet, condition_id, outcome, since):
        row = self.db.execute(
            "SELECT 1 FROM alerts WHERE wallet = ? AND condition_id = ? AND outcome = ? AND ts >= ? LIMIT 1",
            (wallet, condition_id, outcome, since),
        ).fetchone()
        return row is not None

    def save_alert(self, alert):
        self.db.execute(
            "INSERT INTO alerts (ts, wallet, condition_id, outcome, title, price, usd, score, signals)"
            " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            (
                alert["ts"], alert["wallet"], alert["condition_id"], alert["outcome"], alert["title"],
                alert["price"], alert["usd"], alert["score"], json.dumps(alert["signals"], ensure_ascii=False),
            ),
        )
        self.db.commit()

    def list_alerts(self, limit=20):
        rows = self.db.execute("SELECT * FROM alerts ORDER BY ts DESC LIMIT ?", (limit,)).fetchall()
        return [dict(r) for r in rows]
