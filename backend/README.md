# PointMarket Backend

This is the backend service for PointMarket. It is a HTTP API written in Go (Gin). It stores and serves data for users, students, questionnaires, courses, assignments, quizzes, badges, missions, products, and weekly evaluations. It can also call an external AI service for text analysis (optional).

## What it does

- Login and JWT tokens for three roles: admin, guru (teacher), siswa (student).
- Students and programs: list, filter, and update.
- Questionnaires: MSLQ, AMS (Likert), and VARK. Stores results and simple correlations.
- Weekly evaluations: create on schedule or on demand.
- Courses, lessons, assignments, quizzes, and student submissions.
- Points, badges, missions, and basic marketplace.
- Optional text analysis endpoint that forwards to an AI service.

## Folder guide

```
backend/
  cmd/
    api/                      # API main
    scheduler/                # weekly evaluation loop
    init-weekly-evaluations/  # one-time initializer
    import-nim-name/          # import helper
    db/
      migrations/             # SQL migrations
      scripts/dummy_data.sql  # example data
      seed/                   # seed data
  internal/
    handler/                  # HTTP handlers
    services/                 # application logic
    store/gen/                # sqlc output (queries + models)
    middleware/               # auth and role checks
    database/                 # MySQL connector
    config/                   # env loader
    utils/                    # helpers
```

## Setup

1) Copy the example env file and edit values

```
cp backend/.env.example backend/.env
```

Main variables:

| Name | Default | Description |
| --- | --- | --- |
| `DB_HOST` | `localhost` | MySQL host name or service name (e.g., `db` in Docker). |
| `DB_PORT` | `3306` | MySQL port. |
| `DB_USER` | `root` | MySQL user. In Docker, usually `pointmarket`. |
| `DB_PASSWORD` | (empty) | MySQL password. |
| `DB_NAME` | `pointmarket` | Database name. |
| `JWT_SECRET` | `your-secret-key` | Secret for signing JWT tokens. |
| `JWT_EXPIRATION_HOURS` | `72` | Token lifetime in hours. |
| `SERVER_PORT` | `8080` | HTTP port the API listens on. |
| `ALLOWED_ORIGINS` | `http://localhost:8081` | Comma-separated origins allowed by CORS. |
| `AI_SERVICE_URL` | `http://localhost:5000` | Base URL for the AI service. Only used by `/api/v1/text-analyzer`. |

2) Prepare the database

Run migrations (any of these):

- With Goose CLI
```
go install github.com/pressly/goose/v3/cmd/goose@latest
export MYSQL_DSN="user:pass@tcp(127.0.0.1:3306)/pointmarket?parseTime=true"
goose -dir ./backend/cmd/db/migrations mysql "$MYSQL_DSN" up
```

- Manually: apply the SQL files in `backend/cmd/db/migrations` in numeric order.

- Seed optional demo data
```
go run ./backend/cmd/db/seed
```

## Run

### With Docker Compose

```
docker compose up -d db
# wait until db is healthy
docker compose up -d backend
docker compose logs -f backend
```

### Locally (Go)

```
cd backend
go run ./cmd/api
```

Optional:
```
# weekly evaluation scheduler
go run ./cmd/scheduler

# one-time weekly evaluation init
go run ./cmd/init-weekly-evaluations
```

## API quick map

Base path: `/api/v1`

| Resource | Endpoints (short list) | Notes |
| --- | --- | --- |
| Auth | `POST /auth/register`, `POST /auth/login` | Login returns a JWT. |
| Profile | `GET /profile`, `PUT /profile`, `PUT /profile/password`, `GET /roles` | Requires JWT. |
| Programs | `GET /programs` | List academic programs. |
| Students | `GET /students`, `GET /students/:user_id`, `PUT /students/:user_id` | Filters: search, program, cohort, status. Admin for write. |
| Questionnaires | `GET /questionnaires`, `GET /questionnaires/:id`, `POST /questionnaires/likert`, `POST /questionnaires/vark`, `GET /questionnaires/correlations` | Admin CRUD also available. |
| Weekly evaluations | `GET /weekly-evaluations`, `POST /weekly-evaluations/initialize` | Init is admin-only. |
| Text analyzer | `POST /text-analyzer` | Calls `AI_SERVICE_URL`. Optional. |
| Courses | `GET /courses`, `GET /courses/:id`, `POST/PUT/DELETE /courses/:id`, `POST /courses/:id/enroll`, `DELETE /courses/:id/unenroll` | Admin/teacher for write. |
| Assignments | `POST/GET/PUT/DELETE /assignments`, student start/submit, view/update submissions | Write is admin/teacher. |
| Quizzes | CRUD + questions; student start/submit; view/update submissions | Write is admin/teacher. |
| Badges | `GET /badges`, admin CRUD, `POST /badges/:id/award`, `DELETE /badges/:id/revoke`, `GET /my-badges` | Award/revoke admin-only. |
| Missions | `GET /missions`, admin CRUD, `POST /missions/:id/start`, `PUT /missions/:id/status` | Start/status needs auth. |
| Products & categories | List + admin CRUD, `POST /products/:id/purchase` | Marketplace actions. |
| Health | `GET /health` | Returns `{"status":"ok"}`. |

All protected routes expect `Authorization: Bearer <token>`.

## Run with Air (auto reload)

1) Install Air (once):
```
go install github.com/cosmtrek/air@latest
```

2) From project root or `backend/` folder:
```
cd backend
cp .env.example .env   # adjust DB settings
air -c .air.toml
```

This watches files and restarts the server on changes.

## Import helper (CSV NIM + NAMA)

Create users and students from a CSV that contains columns for NIM/NPM and NAMA.

```
go run ./backend/cmd/import-nim-name \
  -file RekapDataMahasiswaAktifTI.csv \
  -email-domain campus.ac.id \
  -program-name "D4 Teknik Informatika" \
  -cohort-year 2024 \
  -dry-run
```

Then remove `-dry-run` to write data.

Notes:
- Email is generated as `<nim>@<email-domain>`.
- Password defaults to NIM unless `-password` is set.

## Tests

```
cd backend
go test ./...
```
