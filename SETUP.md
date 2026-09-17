# Lab Utilization System — Setup Guide

> **Environment:** Docker (Windows + WSL2 / Ubuntu 24.04)
> Same Docker setup as the Faculty Portfolio project.

---

## Prerequisites

- Docker Desktop for Windows (running)
- WSL 2 with Ubuntu 24.04
- Docker Desktop WSL Integration enabled for Ubuntu-24.04

### Enable WSL Integration (one-time)

1. Open **Docker Desktop**
2. Go to **Settings → Resources → WSL Integration**
3. Toggle **Ubuntu-24.04** ON
4. Click **Apply & Restart**
5. Open your Ubuntu terminal and verify:
   ```bash
   docker --version   # should print Docker version
   ```

---

## First-Time Start (3 commands)

```bash
# 1. Go to the project folder
cd /home/jomar/dev/projects/LabSystemUtilization/lab-utilization-system

# 2. Build and start all containers
make build

# 3. Wait ~2-3 minutes for the first build, then open:
#    http://localhost:8088
```

The entrypoint script runs automatically and:
- Installs PHP dependencies (`composer install`)
- Copies `.env.docker` → `.env`
- Generates the app key
- Waits for MySQL to be ready
- Runs all migrations
- Seeds the database (20 rooms + default accounts)
- Builds frontend assets (Vite/Tailwind)

---

## Every Day After That

```bash
make up      # start containers
make down    # stop containers
make logs    # watch logs live
```

---

## Default Login Accounts

| Role | Email | Password |
|---|---|---|
| Lab Head (Admin) | `labhead@pup.edu.ph` | `ChangeMe123!` |
| Student Assistant | `assistant@pup.edu.ph` | `ChangeMe123!` |

> ⚠️ Change these passwords immediately after first login.

---

## Useful Commands

```bash
make shell                          # bash inside the app container
make db                             # MySQL shell
make artisan cmd="route:list"       # run any artisan command
make artisan cmd="migrate:status"   # check migration status
make fresh                          # ⚠️ wipe all data and re-seed
make overdue                        # dry-run overdue check
```

---

## Environment Configuration

The file **`.env.docker`** is pre-configured for Docker (no editing needed for basic use).

To enable **Telegram notifications**, edit `.env` inside the container:

```bash
make shell
nano .env
```

Set these values:
```
TELEGRAM_BOT_TOKEN=123456:ABC-your-bot-token
TELEGRAM_DEFAULT_CHAT_ID=-100your_group_id
```

To enable the **Google Form API**:
```
API_SECRET_KEY=some-long-random-secret-here
```
Copy the same value into your Apps Script → Project Settings → Script Properties:
- Key: `LARAVEL_API_KEY`
- Value: `some-long-random-secret-here`

---

## Port Reference

| Service | Port | URL |
|---|---|---|
| Laravel (Nginx) | 8088 | http://localhost:8088 |
| MySQL | 3308 | `127.0.0.1:3308` (DB tool) |

> Port 8088 is used to avoid conflict with the faculty portfolio (8081).

---

## Connecting a DB Tool (TablePlus / DBeaver)

| Field | Value |
|---|---|
| Host | `127.0.0.1` |
| Port | `3308` |
| Database | `lab_utilization` |
| Username | `lab_user` |
| Password | `lab_password` |

---

## Overdue Alert Schedule (Cron)

The overdue Artisan command (`lab:check-overdue`) is scheduled every 15 minutes.
To activate, run the scheduler inside the container:

```bash
# Option A: Run inside the container as a background process
make shell
php artisan schedule:work &

# Option B: Add to the host cron (runs outside Docker)
# crontab -e
# * * * * * docker exec lab-utilization-app php artisan schedule:run >> /dev/null 2>&1
```

---

## Project Structure (key files)

```
lab-utilization-system/
├── docker/
│   ├── php/
│   │   ├── Dockerfile          # PHP 8.3-FPM image
│   │   └── entrypoint.sh       # Auto-setup on container start
│   └── nginx/
│       ├── Dockerfile          # Nginx 1.27 image
│       └── default.conf        # Laravel routing config
├── docker-compose.yml          # All 3 services (app, web, db)
├── Makefile                    # Shortcut commands
├── .env.docker                 # Pre-filled Docker env (no editing needed)
├── google-apps-script/
│   └── LabSubmission.js        # Paste into Google Form Apps Script
└── routes/
    ├── web.php                 # All web routes (scan, dashboard, admin)
    ├── api.php                 # POST /api/submission (Google Form)
    └── console.php             # Scheduled commands
```

---

## Quick URL Reference

| URL | Who | What |
|---|---|---|
| `http://localhost:8088` | Everyone | Homepage → redirect to login |
| `http://localhost:8088/dashboard` | All logged in | Main dashboard |
| `http://localhost:8088/admin/tools` | Lab Head | Manage tool inventory |
| `http://localhost:8088/admin/qr/rooms` | Lab Head | Print room QR codes |
| `http://localhost:8088/admin/qr/tools` | Lab Head | Print tool QR codes |
| `http://localhost:8088/admin/transactions` | Lab Head, SA | Transaction history |
| `http://localhost:8088/admin/reports` | Lab Head | Charts & analytics |
| `http://localhost:8088/scan/room/{id}` | Public (QR) | Room checkout |
| `http://localhost:8088/scan/tool/{id}` | Public (QR) | Tool checkout |
| `http://localhost:8088/api/submission` | Google Form | POST endpoint |
