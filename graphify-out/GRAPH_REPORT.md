# Graph Report - CEO Dashboard  (2026-11-21)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 27 nodes · 43 edges · 5 communities
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
- Community 4

## God Nodes (most connected - your core abstractions)
1. `CEO Dashboard` - 6 edges
2. `Phase 2 daily brief` - 6 edges
3. `Client ownership and visibility` - 5 edges
4. `Phase 1 Client Sites hub` - 5 edges
5. `Security and release gates` - 5 edges
6. `Phased roadmap` - 5 edges
7. `Agent handoff` - 4 edges
8. `Product requirements` - 4 edges
9. `Sensitive operation audit` - 4 edges
10. `Phase 4 client portal` - 4 edges

## Surprising Connections (you probably didn't know these)
- `CEO Dashboard` --prioritizes--> `Today priorities`  [EXTRACTED]
  readme.md → PRD.md
- `Security and release gates` --gates--> `GitHub milestone workflow`  [EXTRACTED]
  PRD.md → readme.md
- `Agent handoff` --reads--> `Project decisions`  [EXTRACTED]
  AGENTS.md → Decisions.md
- `Agent handoff` --follows--> `GitHub milestone workflow`  [EXTRACTED]
  AGENTS.md → readme.md
- `Agent handoff` --reads--> `Product requirements`  [EXTRACTED]
  AGENTS.md → PRD.md

## Communities (5 total, 0 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.43
Nodes (7): Agent handoff, CEO Dashboard, Project decisions, GitHub milestone workflow, Decisions to confirm, Product requirements, Obsidian vault

### Community 1 - "Community 1"
Cohesion: 0.50
Nodes (5): Propose then approve, Sensitive operation audit, Client ownership and visibility, Dated client follow-ups, Today priorities

### Community 2 - "Community 2"
Cohesion: 0.33
Nodes (6): Phase 2 daily brief, 10 AM Manila cron, Brief delivery and recovery, Hostinger Web or Cloud, Provider-agnostic model adapter, Laravel and MySQL

### Community 3 - "Community 3"
Cohesion: 0.47
Nodes (6): Phase 3 approved actions, Phase 1 Client Sites hub, Client and site payments, Phase 4 client portal, Phased roadmap, Site change-log vault

### Community 4 - "Community 4"
Cohesion: 0.67
Nodes (3): AES-256-GCM encryption, Gmail read-only OAuth, Security and release gates

## Knowledge Gaps
- **4 isolated node(s):** `Decisions to confirm`, `Obsidian vault`, `Brief delivery and recovery`, `Laravel and MySQL`
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 4 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Phase 2 daily brief` connect `Community 2` to `Community 4`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
- **What connects `Decisions to confirm`, `Obsidian vault`, `Brief delivery and recovery` to the rest of the system?**
  _4 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Why does `Phased roadmap` connect `Community 3` to `Community 2`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `Product requirements` connect `Community 0` to `Community 3`, `Community 4`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
