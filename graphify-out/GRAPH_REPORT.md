# Graph Report - CEO Dashboard  (2026-11-21)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 26 nodes · 40 edges · 5 communities
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3
- Community 4

## God Nodes (most connected - your core abstractions)
1. `CEO Dashboard` - 6 edges
2. `Phase 2 daily brief` - 6 edges
3. `Phase 1 Client Sites hub` - 5 edges
4. `Security and release gates` - 5 edges
5. `Phased roadmap` - 5 edges
6. `Product requirements` - 4 edges
7. `Agent handoff` - 4 edges
8. `Client ownership and visibility` - 4 edges
9. `Phase 4 client portal` - 4 edges
10. `Project decisions` - 3 edges

## Surprising Connections (you probably didn't know these)
- `CEO Dashboard` --prioritizes--> `Today priorities`  [EXTRACTED]
  readme.md → PRD.md
- `Security and release gates` --gates--> `GitHub milestone workflow`  [EXTRACTED]
  PRD.md → readme.md
- `CEO Dashboard` --resumed_using--> `Agent handoff`  [EXTRACTED]
  readme.md → AGENTS.md
- `CEO Dashboard` --records_decisions_in--> `Project decisions`  [EXTRACTED]
  readme.md → Decisions.md
- `CEO Dashboard` --specified_by--> `Product requirements`  [EXTRACTED]
  readme.md → PRD.md

## Communities (5 total, 0 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.43
Nodes (7): Agent handoff, CEO Dashboard, Project decisions, GitHub milestone workflow, Decisions to confirm, Product requirements, Obsidian vault

### Community 1 - "Community 1"
Cohesion: 0.38
Nodes (7): Propose then approve, Sensitive operation audit, Client ownership and visibility, AES-256-GCM encryption, Gmail read-only OAuth, Phase 1 Client Sites hub, Security and release gates

### Community 2 - "Community 2"
Cohesion: 0.50
Nodes (5): Phase 3 approved actions, Phase 2 daily brief, Brief delivery and recovery, Provider-agnostic model adapter, Phased roadmap

### Community 3 - "Community 3"
Cohesion: 0.50
Nodes (4): Client and site payments, Phase 4 client portal, Today priorities, Site change-log vault

### Community 4 - "Community 4"
Cohesion: 0.67
Nodes (3): 10 AM Manila cron, Hostinger Web or Cloud, Laravel and MySQL

## Knowledge Gaps
- **4 isolated node(s):** `Obsidian vault`, `Brief delivery and recovery`, `Laravel and MySQL`, `Decisions to confirm`
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 4 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Phase 2 daily brief` connect `Community 2` to `Community 1`, `Community 4`?**
  _High betweenness centrality (0.065) - this node is a cross-community bridge._
- **What connects `Obsidian vault`, `Brief delivery and recovery`, `Laravel and MySQL` to the rest of the system?**
  _4 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Why does `Phased roadmap` connect `Community 2` to `Community 1`, `Community 3`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Why does `Product requirements` connect `Community 0` to `Community 1`, `Community 2`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
