# Insider bot — Polymarket

Ce bot surveille en continu les trades publics de Polymarket. Il repère les paris qui ressemblent à ceux d'un **initié** et t'envoie une alerte en console, sur Telegram ou sur Discord.

> **Alertes uniquement.** Le bot ne trade pas et ne demande jamais de clé privée ni de wallet. C'est voulu : copier automatiquement les initiés, c'est s'exposer aux wallets « appâts » et à servir de liquidité de sortie.

## Comment il décide

Pour chaque trade d'au moins `min_trade_usd` (5 000 $ par défaut), le bot récupère l'historique du wallet et les infos du marché, puis il calcule un score. Si le score atteint `alert_score` (7 par défaut), il envoie une alerte.

| Signal | Points | Pourquoi |
|---|---|---|
| Gros pari (≥ 4 × le minimum) / pari important | +2 / +1 | Un initié mise gros |
| Wallet neuf (< 7 j) / jeune (< 30 j) | +3 / +1 | Wallet créé exprès pour ce pari |
| A joué sur ≤ 3 marchés | +2 | Pas un parieur « normal » |
| Pari = ≥ 50 % de son volume total | +1 | Tout sur un seul coup |
| Achat à ≤ 0.25 / ≤ 0.40 | +2 / +1 | Mise sur une issue que le marché juge improbable |
| Le marché se termine dans ≤ 72 h | +2 | L'info va bientôt tomber |
| ≥ 3 / 2 wallets neufs sur la même issue en 24 h | +3 / +1 | Initié qui découpe sa mise sur plusieurs wallets |
| Trader régulier (≥ 200 trades) | −3 | Gros joueur connu, pas un initié ponctuel |
| Vente | −1 | Moins informatif qu'un achat |

Le bot ignore aussi ces cas :

- les achats à **0.95 ou plus** (du farming de fin de marché) ;
- les marchés crypto « Up or Down » ultra-courts (du bruit) ;
- les petits trades.

Il ne réalerte pas sur le même wallet et la même issue pendant 24 h.

## Installation

Il faut seulement Python 3.10 ou plus récent, sans aucune dépendance externe.

```bash
cd insider-bot
python3 -m insider_bot run --fixtures fixtures   # démo hors-ligne avec des données fictives
python3 -m insider_bot run                       # surveillance réelle, en continu
python3 -m insider_bot run --once                # un seul cycle
python3 -m insider_bot alerts                    # historique des alertes (SQLite)
python3 -m unittest discover -s tests            # tests
```

### Alertes Telegram

1. Crée un bot avec [@BotFather](https://t.me/BotFather) et récupère son token.
2. Envoie un message à ton bot, puis ouvre `https://api.telegram.org/bot<TOKEN>/getUpdates` pour trouver ton `chat_id`.
3. Lance le bot avec ces variables :

```bash
export TELEGRAM_BOT_TOKEN="123:ABC..."
export TELEGRAM_CHAT_ID="123456789"
python3 -m insider_bot test-notify   # vérifie que le message arrive
python3 -m insider_bot run
```

Pour Discord, définis `DISCORD_WEBHOOK_URL`.

### Réglages

Copie `config.example.json` en `config.json`, modifie-le, puis lance `python3 -m insider_bot --config config.json run`. Tous les seuils sont dans `insider_bot/config.py`.

- **Trop d'alertes ?** Monte `alert_score` à 9 ou `min_trade_usd` à 10 000.
- **Pas assez ?** Baisse-les.

## Limites (à lire)

- **Un signal n'est pas une preuve.** Beaucoup d'alertes seront des parieurs chanceux ou des baleines qui créent un nouveau wallet.
- Les initiés malins savent qu'ils sont surveillés. Ils peuvent étaler leurs mises ou utiliser de vieux wallets.
- Le bot ne remonte pas l'origine des fonds sur la blockchain Polygon. Une piste d'amélioration : utiliser l'API Polygonscan pour relier les wallets financés par la même adresse.
- **Juridique :** surveiller des données publiques est légal. En revanche, trader toi-même avec une information privilégiée réelle peut constituer un délit d'initié. Vérifie aussi que Polymarket est accessible légalement depuis ton pays : il est restreint en France.

## Structure

```
insider_bot/
  api.py       clients API Polymarket (data-api + gamma-api) et client « fixtures » hors-ligne
  scoring.py   profil du wallet et calcul du score
  bot.py       boucle : collecte → dédoublonnage → profil → score → alerte
  store.py     SQLite : trades vus, cache des wallets, clusters, alertes
  notify.py    console / Telegram / Discord
  config.py    paramètres
fixtures/      scénarios de démo (cluster de wallets neufs, baleine, farming…)
tests/
```
