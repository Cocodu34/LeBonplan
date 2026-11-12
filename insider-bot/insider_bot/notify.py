"""Envoi des alertes : console, Telegram, Discord."""

import html
import json
import logging
import urllib.request

log = logging.getLogger(__name__)


def format_alert(alert, rich=False):
    esc = html.escape if rich else (lambda s: s)
    bold = (lambda s: f"<b>{esc(s)}</b>") if rich else (lambda s: s)
    lines = [
        f"🚨 {bold('Pari suspect')} — score {alert['score']}",
        f"📊 {esc(alert['title'] or alert['condition_id'])}",
        f"🎯 {alert['side']} « {esc(alert['outcome'])} » à {alert['price']:.2f} — {alert['usd']:,.0f} $",
        f"👛 {alert['wallet']}",
        "",
        "Signaux :",
    ]
    for s in alert["signals"]:
        sign = "+" if s["points"] >= 0 else ""
        lines.append(f"  {sign}{s['points']} {esc(s['detail'])}")
    lines += [
        "",
        f"🔗 {alert['market_url']}",
        f"🔗 {alert['profile_url']}",
        "⚠️ Signal, pas une certitude. Ne copie pas aveuglément.",
    ]
    return "\n".join(lines)


class ConsoleNotifier:
    def send(self, alert):
        print(format_alert(alert), flush=True)
        print("-" * 60, flush=True)


def _post_json(url, payload, timeout=15):
    req = urllib.request.Request(
        url, data=json.dumps(payload).encode(), headers={"Content-Type": "application/json"}
    )
    with urllib.request.urlopen(req, timeout=timeout) as resp:
        return resp.status


class TelegramNotifier:
    def __init__(self, token, chat_id):
        self.url = f"https://api.telegram.org/bot{token}/sendMessage"
        self.chat_id = chat_id

    def send(self, alert):
        _post_json(self.url, {
            "chat_id": self.chat_id,
            "text": format_alert(alert, rich=True),
            "parse_mode": "HTML",
            "disable_web_page_preview": True,
        })


class DiscordNotifier:
    def __init__(self, webhook_url):
        self.url = webhook_url

    def send(self, alert):
        _post_json(self.url, {"content": format_alert(alert)[:2000]})


def build_notifiers(cfg):
    notifiers = [ConsoleNotifier()]
    if cfg["telegram_bot_token"] and cfg["telegram_chat_id"]:
        notifiers.append(TelegramNotifier(cfg["telegram_bot_token"], cfg["telegram_chat_id"]))
    if cfg["discord_webhook_url"]:
        notifiers.append(DiscordNotifier(cfg["discord_webhook_url"]))
    return notifiers


def broadcast(notifiers, alert):
    for n in notifiers:
        try:
            n.send(alert)
        except Exception as exc:  # une notif qui échoue ne doit pas arrêter le bot
            log.warning("Échec de notification via %s : %s", type(n).__name__, exc)
