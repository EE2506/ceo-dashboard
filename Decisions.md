# Decisions

## Agreed direction

| Decision | Reason |
| --- | --- |
| Laravel/PHP and MySQL on Hostinger Web or Cloud | Fits DJ’s existing workflow and the target environment. Design for bounded cron jobs without resident workers. |
| Client Sites hub first | Useful for client walkthroughs and tracking payments before Gmail is connected. |
| Client ownership and internal visibility from day one | A later client portal needs a clear isolation boundary. Private links and internal records need an explicit publication choice. |
| 10:00 AM Manila brief, Gmail read-only, plain template first | Lets DJ validate the daily workflow without trusting a model with mailbox writes. |
| Model proposals require approval before writes | DJ can review the target and exact change while Laravel retains permission and execution control. |
| Public GitHub repository with a security review at every milestone | DJ chose public source on November 21, 2026. Publish synthetic fixtures and public-safe docs only. Runtime secrets and production data stay private. |

## Proposed defaults to confirm before the relevant phase

- **Phase 1** — Check whether Filament fits the selected Hostinger PHP runtime. Confirm one payment can be recorded at client level with an optional site. Choose a public redistribution license and confirm whether a push should deploy.
- **Phase 2** — Confirm which Google account actually receives own-domain mail, its OAuth audience, and the outbound sender. Start with a manually applied Gmail label, dated follow-ups, and unpaid items with due dates. Send a generic notification with a private dashboard link.
- **Phase 3** — Select a provider and retention policy for DJ’s model. The first approved write creates an internal follow-up.
- **Phase 4** — Confirm who may view each client’s records, what historical entries may be shared, and whether invoice links remain private when copied. Support multiple named contacts per client if the pilot requires it.

Update this note with the date and reason when DJ confirms or changes a decision. Update the [PRD](PRD.md) when a decision changes a requirement. Return to [Start here](readme.md).
