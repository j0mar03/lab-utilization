# ─────────────────────────────────────────────────────────────────────────────
# Lab Utilization System — Makefile
# Usage: make <command>
# ─────────────────────────────────────────────────────────────────────────────

.PHONY: help up down restart build logs shell db artisan fresh seed overdue

# Default: show help
help:
	@echo ""
	@echo "  Lab Utilization System — Docker Commands"
	@echo "  ─────────────────────────────────────────"
	@echo "  make up        Start all containers (builds if needed)"
	@echo "  make down      Stop and remove containers"
	@echo "  make restart   Restart containers"
	@echo "  make build     Rebuild containers from scratch"
	@echo "  make logs      Follow container logs"
	@echo "  make shell     Open a bash shell inside the app container"
	@echo "  make db        Open a MySQL shell"
	@echo "  make artisan   Run an artisan command  e.g. make artisan cmd='route:list'"
	@echo "  make fresh     Drop all tables, re-migrate, re-seed (⚠️ destroys data)"
	@echo "  make seed      Run database seeders only"
	@echo "  make overdue   Check for overdue transactions now (dry-run)"
	@echo ""

# ── Start ─────────────────────────────────────────────────────────────────
up:
	docker compose up -d
	@echo ""
	@echo "✅ Started! Open → http://localhost:8088"
	@echo "   Logs: make logs"

# ── Stop ──────────────────────────────────────────────────────────────────
down:
	docker compose down

# ── Restart ───────────────────────────────────────────────────────────────
restart:
	docker compose restart

# ── Rebuild (force fresh image build) ─────────────────────────────────────
build:
	docker compose build --no-cache
	docker compose up -d

# ── Logs ──────────────────────────────────────────────────────────────────
logs:
	docker compose logs -f

# ── Shell inside app container ────────────────────────────────────────────
shell:
	docker compose exec app bash

# ── MySQL shell ───────────────────────────────────────────────────────────
db:
	docker compose exec db mysql -u lab_user -plab_password lab_utilization

# ── Run any artisan command ───────────────────────────────────────────────
# Usage: make artisan cmd="migrate:status"
artisan:
	docker compose exec app php artisan $(cmd)

# ── Fresh install (⚠️ wipes all data) ────────────────────────────────────
fresh:
	@echo "⚠️  This will wipe all data. Press Ctrl+C to cancel, Enter to continue."
	@read _confirm
	docker compose exec app php artisan migrate:fresh --seed --force

# ── Run seeders only ──────────────────────────────────────────────────────
seed:
	docker compose exec app php artisan db:seed --force

# ── Check overdue transactions now ────────────────────────────────────────
overdue:
	docker compose exec app php artisan lab:check-overdue --dry-run
