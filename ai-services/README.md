# AI Service

This service provides a small HTTP API to analyze text and produce simple VARK‑style scores along with keywords, key sentences, and some text metrics. It is written in Python (Flask) and uses Stanza for Indonesian NLP when available. It also reads a keyword lexicon from a MySQL database to support the keyword strategy.

## Folder guide

```
ai-services/
  app/
    __init__.py           # Flask app factory
    routes.py             # API blueprint (/nlp/predict)
    database.py           # SQLAlchemy instance
    models.py             # ORM models (nlp_lexicon)
    dtos.py               # Typed dicts for request/response
    services/             # nlp_service, engine, text analysis helpers
    strategies/           # keyword, linguistic, hybrid
    stores/               # lexicon store (SQL)
  scripts/keywords_meta.sql  # SQL to create/fill nlp_lexicon
  config.py               # environment config (dev/prod)
  run.py                  # app entrypoint
  requirements.txt        # Python dependencies
  Dockerfile              # container build
```

## Environment

Set these variables before running. The app reads `APP_SETTING` to choose config: `dev` or `prod` (default: `dev`).

| Name | Default | Description |
| --- | --- | --- |
| `APP_SETTING` | `dev` | Selects `DevelopmentConfig` or `ProductionConfig` in `config.py`. |
| `SERVER_NAME` | `localhost:5000` | Flask server name/port. In Docker use `0.0.0.0:5000`. |
| `DB_HOST` | (none) | MySQL host for the lexicon database. |
| `DB_PORT` | (none) | MySQL port, usually `3306`. |
| `DB_USER` | (none) | MySQL user. |
| `DB_PASSWORD` | (none) | MySQL password. |
| `DB_NAME` | (none) | Database name (e.g., `pointmarket_meta`). |
| `STANZA_RESOURCES_DIR` | `/stanza_resources` | Where Stanza models are stored (used by Dockerfile). |

To load from a local `.env` file in dev, leave `APP_SETTING=dev`. The app calls `dotenv.load_dotenv()` in dev mode.

Example `.env`:
```
APP_SETTING=dev
SERVER_NAME=0.0.0.0:5000
DB_HOST=db
DB_PORT=3306
DB_USER=pointmarket
DB_PASSWORD=password
DB_NAME=pointmarket_meta
```

## Database and lexicon

The service loads a keyword lexicon from table `nlp_lexicon` in `DB_NAME`. Use the provided SQL to create and fill it:

```
# inside the MySQL client
SOURCE ai-services/scripts/keywords_meta.sql;
```

If the lexicon table is empty, the service logs that it failed to load the lexicon and NLP will not be initialized.

## Run

### With Docker Compose

`compose.yaml` defines an `ai-service` that exposes port 8043 on your machine.

```
docker compose up -d ai-service
# Visit health by posting to the API (see below)
```

The Dockerfile tries to pre‑download Stanza Indonesian models during build. If your build environment blocks outbound network, you can:
- Leave the download step (it has `|| true`) and let the service try to use Stanza if models exist.
- Or comment out the download line and mount a volume at `STANZA_RESOURCES_DIR` with pre‑fetched models.

### Locally (Python)

Requirements: Python 3.11+.

```
cd ai-services
python -m venv .venv
source .venv/bin/activate   # Windows: .venv\Scripts\activate
pip install -r requirements.txt

# set env (or create .env)
export APP_SETTING=dev
export SERVER_NAME=0.0.0.0:5000
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_USER=pointmarket
export DB_PASSWORD=password
export DB_NAME=pointmarket_meta

python run.py
# Server listens on 127.0.0.1:5000 by default
```

To download Stanza Indonesian models locally (once):
```
python -c "import stanza; stanza.download('id')"
```

## API

Base path: `/`

| Method | Path | Body | Response |
| --- | --- | --- | --- |
| POST | `/nlp/predict` | JSON: `{ "text": string, "strategy"?: "keyword"|"linguistic"|"hybrid", "context_type"?: string }` | JSON with fields described below. |

Response fields (example):
```
{
  "strategy_used": "hybrid",
  "word_count": 123,
  "scores": { "visual": 0.4, "auditory": 0.2, "reading": 0.25, "kinesthetic": 0.15 },
  "keywords": ["..."],
  "key_sentences": ["..."],
  "text_stats": { "wordCount": 123, "sentenceCount": 8, "avgWordLength": 4.2, "readingTime": 1 },
  "grammar_score": 84.5,
  "readability_score": 78.0,
  "sentiment_score": 55.0,
  "structure_score": 70.0,
  "complexity_score": 62.0
}
```

Notes:
- If Stanza models are loaded, the service uses them for better tokenization, lemmas, and POS.
- If Stanza is not available, it falls back to regex heuristics and still returns a response.
- The `strategy` parameter controls how VARK scores are computed:
  - `keyword`: lexicon matches
  - `linguistic`: simple ratios and density rules
  - `hybrid`: combination of both with small context/interaction biases

## Troubleshooting

- Models not found / slow start:
  - Loading Stanza may take time on first use. Pre‑download models as shown above.
  - Make sure `STANZA_RESOURCES_DIR` is readable in Docker if you use a custom path.
- DB connection errors:
  - Confirm `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, and `DB_NAME` are correct.
  - Make sure the user has access to the schema with `nlp_lexicon`.
- Empty lexicon:
  - Run `scripts/keywords_meta.sql` to fill `nlp_lexicon`.
- Port not reachable in Docker:
  - Ensure `SERVER_NAME=0.0.0.0:5000` and the port is exposed in the compose file.
