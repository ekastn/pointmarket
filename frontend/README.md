# PointMarket Frontend

This is the PHP frontend for PointMarket. It renders pages for login, dashboard, students, questionnaires, courses, assignments, quizzes, badges, missions, products, and more. It calls the Go backend API using an internal HTTP client (Guzzle).

## Folder guide

```
frontend/
  config/            # routes, dependencies, menu
  src/
    Core/           # Router, ApiClient, ViewRenderer, helpers
    Controllers/    # Page controllers (map to routes)
    Services/       # Small API wrappers for backend endpoints
    Views/          # PHP views (admin, siswa, guru, layouts, components)
  public/           # Static assets (js, css) if present
  index.php         # Front controller (entry point)
  Dockerfile        # Apache + PHP 8.4 image
  .htaccess         # URL rewriting to index.php
  composer.json     # PHP dependencies
```

## Environment

Set the backend API base URL. You can use a `.env` file in `frontend/` or set an environment variable. The app reads `API_BASE_URL` from the environment first, then from `.env`, with a default of `http://localhost:8080`.

| Name | Default | Description |
| --- | --- | --- |
| `API_BASE_URL` | `http://localhost:8080` | Base URL of the Go backend API (e.g., `http://localhost:8041`). |

Example `.env`:
```
API_BASE_URL=http://localhost:8041
```

## Run

### With Docker Compose

`compose.yaml` defines a `frontend` service. It maps port 8042 on your machine to port 80 in the container.

```
# Start only DB and backend first if needed
# docker compose up -d db backend

# Start frontend
docker compose up -d frontend

# Visit
http://localhost:8042/
```

Make sure `API_BASE_URL` in compose points to the backend service, for example `http://backend:8041` inside the Docker network. This is already set in the provided compose file.

### Locally (PHP built-in server)

Requirements: PHP 8.1+ and Composer.

```
cd frontend
cp .env.example .env   # set API_BASE_URL to your backend URL
composer install

# Run with PHP built-in server, using index.php as router
php -S 0.0.0.0:8042 -t . index.php

# Open http://localhost:8042/
```

Notes:
- The built-in server line uses `index.php` as the router so pretty URLs work (mimics `.htaccess` rewrites).
- If you serve from Apache or Nginx, set the document root to `frontend/` and enable rewrite to `index.php`.

## Main pages (routes)

These are handled by `config/routes.php` and custom Router.

| Path | Purpose |
| --- | --- |
| `/` | Login form. |
| `/dashboard` | User dashboard. |
| `/profile` | View/update profile and password. |
| `/questionnaires` | List and manage questionnaires (Likert, VARK). |
| `/vark-correlation-analysis` | View correlation results. |
| `/assignments` | Student assignments list. |
| `/quiz` | Student quizzes list. |
| `/weekly-evaluations` | Student weekly evaluations page. |
| `/courses` | Courses list (admin/teacher manage, students enroll). |
| `/missions` | Missions list (admin manage, students start/update). |
| `/badges` | Badges list (admin manage). |
| `/my-badges` | Current user's badges. |
| `/products` | Marketplace products (admin manage, students purchase). |
| `/product-categories` | Product categories (admin manage). |
| `/users` | Users management (admin). |
| `/students` | Students management (admin). |
| `/settings` | Settings (e.g., multimodal threshold). |

Some routes are grouped and require role checks. The Router and controllers enforce login and role via middleware (`AuthMiddleware`).

## How it talks to the backend

- `src/Core/ApiClient.php` wraps Guzzle calls to the backend.
- The JWT from login is stored in memory and added to the `Authorization` header on requests.
- Services in `src/Services/*` organize API calls per domain (Users, Courses, Products, etc.).

## Troubleshooting

- 401/403 errors:
  - Make sure you are logged in and the JWT is attached to requests.
  - Your user must have the role for admin pages.
- CORS or bad base URL:
  - Check `API_BASE_URL` and confirm the backend is reachable from the frontend container or process.
  - In Docker, prefer `http://backend:8041` for `API_BASE_URL`.
- Pretty URLs are not working locally:
  - Use the built-in server command shown above, or configure rewrite to `index.php` in your web server.


- 👨‍🏫 Teacher Management (Assignments & Quizzes)
  - Access via sidebar: Kelola Tugas and Kelola Kuis.
  - Create/edit/delete assignments and quizzes. Course selection is provided via a dropdown of owned courses.
  - Manage quiz questions on the quiz detail page (/quiz/{id}) with add/edit/delete and up/down ordering.

- 🧑‍🎓 Student Quiz Flow
  - Open /quiz to see available quizzes; click Lihat to view details.
  - Start quiz and answer multiple-choice questions with Next/Prev navigation and a soft timer.
  - Submit to mark completion (points awarded per backend rules when applicable).
