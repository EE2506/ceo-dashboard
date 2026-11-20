# CEO Dashboard

A personal dashboard for DJ, a freelance web developer in Davao City. The first release is a Client Sites hub. The next release answers one question at 10 AM Manila time: what needs DJ’s attention today?

**Status** — Planning. Application implementation awaits approval of a Phase 1 plan.

## Start here

- [PRD](PRD.md) defines scope, architecture, security, and acceptance criteria.
- [Decisions](Decisions.md) records agreed directions, defaults, and open questions.
- [Agent instructions](AGENTS.md) explain how to resume work and close a milestone.
- [Graphify report](graphify-out/GRAPH_REPORT.md) and [Obsidian canvas](graphify-out/obsidian/graph.canvas) index the planning notes. Confirm graph results against the source documents.

## Open in Obsidian

Choose **Open folder as vault** and select this `CEO Dashboard` folder. Start with this note. The PRD, decisions, and agent instructions are linked here. Use the Graphify canvas for a visual map of the planning notes. A fresh AI session should open this same project folder and read [Agent instructions](AGENTS.md) to pick up the agreed context.

## Milestones

Before starting implementation, inspect the project, show a short plan, and wait for DJ’s approval. For every completed milestone, update the documentation and refresh the graph if the sources changed. Run lint, applicable type checks, focused tests, and a staged secret review. Make a focused Git commit, push to the agreed public GitHub branch, and report the commit hash and verification. Keep deployment configuration private and confirm separately whether pushes should trigger deployment.

Keep real client records, mailbox content, OAuth tokens, keys, environment files, and database exports out of Git and the vault. An `.env.example` may contain placeholder values. The repository is public. A redistribution license is pending DJ’s selection. Obsidian stores the notes as local files, so future AI sessions can recover project context by reading them.

## Refresh the graph

The initial semantic index was curated from the four planning notes. After those notes change, re-extract or update the semantic nodes and source references in `graphify-out/graph.json` before rebuilding. Run `graphify cluster-only . --no-label`, then `graphify export obsidian --dir graphify-out/obsidian` from the project root. In the exported canvas, prefix file-card paths with `graphify-out/obsidian/` so they resolve from this vault’s root. Verify the canvas paths and run a Graphify query before committing the refresh.
