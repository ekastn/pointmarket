# PointMarket

PointMarket is a small learning platform made of three parts:
- a Go API (backend)
- a PHP web app (frontend)
- a Python service for text analysis (ai-service)

A MySQL database stores the data. Docker Compose is provided to run everything.

## Services

| Service | Path | Image/Runtime | Port (host → container) | Notes |
| --- | --- | --- | --- | --- |
| Backend | `backend/` | Go + Gin | `8041 → 8041` | REST API, JWT auth, LMS, questionnaires, marketplace. |
| Frontend | `frontend/` | PHP 8.4 + Apache | `8042 → 80` | Web UI; calls backend API. |
| AI Service  | `ai-services/` | Python + Flask | `8043 → 5000` | Text analysis; used by `/api/v1/text-analyzer`. |

## Quick start (Docker Compose)

```
# From the project root

# 1) Start the database
docker compose up -d db

# 2) Wait for DB to be healthy, then start backend
docker compose up -d backend

# 3) Start the frontend
docker compose up -d frontend

# 4) Start the AI service if you plan to use text analysis
docker compose up -d ai-service
```

URLs:
- Frontend: http://localhost:8042/
- Backend health: http://localhost:8041/health

## Environment

Compose sets the most important values. Each service also has its own README with details.

| Service | Key variables | Where to set |
| --- | --- | --- |
| Backend | `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `ALLOWED_ORIGINS`, `SERVER_PORT`, `AI_SERVICE_URL` | `compose.yaml` or `backend/.env` |
| Frontend | `API_BASE_URL` | `compose.yaml` or `frontend/.env` |
| AI Service | `APP_SETTING`, `SERVER_NAME`, `DB_*`, `STANZA_RESOURCES_DIR` | `compose.yaml` or `ai-services/.env` |

See:
- backend/README.md
- frontend/README.md
- ai-services/README.md

## Database

- SQL migrations live in `backend/cmd/db/migrations/`.
- Example data is in `backend/cmd/db/scripts/dummy_data.sql`.
- A small seeder tool is included.

Seed example:
```
go run ./backend/cmd/db/seed
```

## Import students from CSV (NIM + NAMA)

A CLI is provided to create users and students from a CSV with two columns (NIM/NPM and NAMA).

```
go run ./backend/cmd/import-nim-name \
  -file RekapDataMahasiswaAktifTI.csv \
  -email-domain campus.ac.id \
  -program-name "D4 Teknik Informatika" \
  -cohort-year 2024 \
  -dry-run
```

Then remove `-dry-run` to import.

## Development

Backend (auto reload with Air):
```
cd backend
cp .env.example .env   # set DB connection
go install github.com/cosmtrek/air@latest
air -c .air.toml
```

Frontend (PHP built‑in server):
```
cd frontend
cp .env.example .env   # set API_BASE_URL to backend
composer install
php -S 0.0.0.0:8042 -t . index.php
```

AI Service (optional):
```
cd ai-services
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
python run.py
```
