# CEO Dashboard agent instructions

1. Start with [readme.md](readme.md), then read [PRD.md](PRD.md) and [Decisions.md](Decisions.md). The [Graphify report](graphify-out/GRAPH_REPORT.md) is an index. Verify findings against the linked source notes.
2. Phase 1 uses Laravel 13, Filament 5, PHP 8.4, and MySQL. Before starting another implementation milestone, show DJ a brief plan and wait for approval. Confirm stack changes with DJ. Ask one focused question when a decision blocks progress. Get explicit confirmation before deleting files.
3. Preserve phase boundaries, client ownership, default-internal visibility, and the security gates. Follow PSR-12 for PHP and TypeScript strict mode if TypeScript is introduced. Validate input at trust boundaries and use Laravel policies for authorization.
4. Keep private mailbox and client data, credentials, tokens, keys, deployment secrets, and database exports out of notes, logs, Graphify input, and Git. Use synthetic fixtures.
5. When closing a milestone, update the PRD or Decisions to reflect approved changes and refresh the Graphify semantic index and Obsidian export if the sources changed. Keep generated note links inside the vault. Run lint, applicable type checks, focused tests, and a staged secret review. Commit and push to the agreed public GitHub branch and report the commit and verification. Review everything staged for public exposure, including generated artifacts. Check Git status and the configured destination before pushing.

Use only `readme.md`, `PRD.md`, `Decisions.md`, and `AGENTS.md` as semantic graph sources. Reconfirm that future Graphify input is safe to index before expanding it.
