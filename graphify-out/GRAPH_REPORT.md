# Graph Report - CEO Dashboard  (2026-11-21)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 28 nodes · 48 edges · 4 communities
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `347bc902`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3

## God Nodes (most connected - your core abstractions)
1. `CEO Dashboard` - 7 edges
2. `Phase 2 daily brief` - 6 edges
3. `Today priorities` - 5 edges
4. `Sensitive operation audit` - 5 edges
5. `Client ownership and visibility` - 5 edges
6. `Phase 1 Client Sites hub` - 5 edges
7. `Phased roadmap` - 5 edges
8. `Security and release gates` - 5 edges
9. `Agent handoff` - 4 edges
10. `Product requirements` - 4 edges

## Surprising Connections (you probably didn't know these)
- `CEO Dashboard` --includes--> `Owner analytics`  [EXTRACTED]
  readme.md → PRD.md
- `CEO Dashboard` --prioritizes--> `Today priorities`  [EXTRACTED]
  readme.md → PRD.md
- `Agent handoff` --reads--> `Project decisions`  [EXTRACTED]
  AGENTS.md → Decisions.md
- `Agent handoff` --follows--> `GitHub milestone workflow`  [EXTRACTED]
  AGENTS.md → readme.md
- `Agent handoff` --reads--> `Product requirements`  [EXTRACTED]
  AGENTS.md → PRD.md

## Communities (4 total, 0 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.39
Nodes (8): Agent handoff, CEO Dashboard, Project decisions, GitHub milestone workflow, Decisions to confirm, Product requirements, Security and release gates, Obsidian vault

### Community 1 - "Community 1"
Cohesion: 0.83
Nodes (4): Owner analytics, Dated client follow-ups, Client and site payments, Today priorities

### Community 2 - "Community 2"
Cohesion: 0.29
Nodes (7): Phase 2 daily brief, 10 AM Manila cron, Brief delivery and recovery, AES-256-GCM encryption, Gmail read-only OAuth, Hostinger Web or Cloud, Laravel and MySQL

### Community 3 - "Community 3"
Cohesion: 0.33
Nodes (9): Phase 3 approved actions, Propose then approve, Sensitive operation audit, Client ownership and visibility, Phase 1 Client Sites hub, Provider-agnostic model adapter, Phase 4 client portal, Phased roadmap (+1 more)

## Knowledge Gaps
- **4 isolated node(s):** `Decisions to confirm`, `Obsidian vault`, `Brief delivery and recovery`, `Laravel and MySQL`
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 4 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Phased roadmap` connect `Community 3` to `Community 2`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **What connects `Decisions to confirm`, `Obsidian vault`, `Brief delivery and recovery` to the rest of the system?**
  _4 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Why does `Product requirements` connect `Community 0` to `Community 3`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
