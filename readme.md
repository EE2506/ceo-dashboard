# CEO Dashboard

A personal dashboard for DJ, a freelance web developer in Davao City. The first release is a Client Sites hub. The next release answers one question at 10 AM Manila time: what needs DJ’s attention today?

**Status** — Phase 1 implements the owner-only Client Sites hub. Phase 2 remains in planning.

## Start here

- [PRD](PRD.md) defines scope, architecture, security, and acceptance criteria.
- [Decisions](Decisions.md) records agreed directions, defaults, and open questions.
- [Agent instructions](AGENTS.md) explain how to resume work and close a milestone.
- [Graphify report](graphify-out/GRAPH_REPORT.md) and [Obsidian canvas](graphify-out/obsidian/graph.canvas) index the planning notes. Confirm graph results against the source documents.

## Run the Client Sites hub

Use PHP 8.4 with current patches, Composer 2, and MySQL 8. PHP needs PDO MySQL and the extensions resolved by Composer, including OpenSSL, intl, mbstring, and ZIP. Enable PDO SQLite to run the default test suite. Filament generates its assets during Composer installation.

1. Copy `.env.example` to `.env` and enter the details of an empty local MySQL database. For localhost HTTP development, set `APP_ENV=local`, `APP_URL=http://127.0.0.1:8000`, and `SESSION_SECURE_COOKIE=false` in that local file.
2. Run `composer install`, then check the runtime with `composer check-platform-reqs`.
3. Run `php artisan key:generate` once for the new installation, then `php artisan migrate` and `php artisan dashboard:make-owner`. Choose a non-email username and enter the password at the hidden prompt. Protect the application key and retain it with encrypted backups.
4. Run `php artisan serve` and open `http://127.0.0.1:8000/admin`. Use the owner account to manage clients, sites, change entries, and payment records. Use each site's **Change log** action for a filtered walkthrough.

Run `composer lint` and `composer test` before pushing changes.

## Hostinger deployment

Verify that the target hPanel account offers current PHP 8.4 on both the site and CLI, the required extensions, MySQL, and a private way to run migrations. Set the web document root to this app's public directory or place only its public entry point and published assets in the panel's web root. Confirm that requests for environment files, Composer metadata, storage, and source return no files. Set APP_ENV to production, APP_DEBUG to false, APP_URL to the HTTPS origin, and SESSION_SECURE_COOKIE and SESSION_ENCRYPT to true. Enable the HTTPS redirect at the hosting layer. Install production dependencies with composer install --no-dev --optimize-autoloader, then migrate and provision the owner over private CLI access.

## Open in Obsidian

Choose **Open folder as vault** and select this `CEO Dashboard` folder. Start with this note. The PRD, decisions, and agent instructions are linked here. Use the Graphify canvas for a visual map of the planning notes. A fresh AI session should open this same project folder and read [Agent instructions](AGENTS.md) to pick up the agreed context.

## Milestones

Before starting implementation, inspect the project, show a short plan, and wait for DJ’s approval. For every completed milestone, update the documentation and refresh the graph if the sources changed. Run lint, applicable type checks, focused tests, and a staged secret review. Make a focused Git commit, push to the agreed public GitHub branch, and report the commit hash and verification. Keep deployment configuration private and confirm separately whether pushes should trigger deployment.

Keep real client records, mailbox content, OAuth tokens, keys, environment files, and database exports out of Git and the vault. An `.env.example` may contain placeholder values. The repository is public. A redistribution license is pending DJ’s selection. Obsidian stores the notes as local files, so future AI sessions can recover project context by reading them.

## Refresh the graph

The initial semantic index was curated from the four planning notes. After those notes change, re-extract or update the semantic nodes and source references in `graphify-out/graph.json` before rebuilding. Run `graphify cluster-only . --no-label`, then `graphify export obsidian --dir graphify-out/obsidian` from the project root. In the exported canvas, prefix file-card paths with `graphify-out/obsidian/` so they resolve from this vault’s root. Verify the canvas paths and run a Graphify query before committing the refresh.
