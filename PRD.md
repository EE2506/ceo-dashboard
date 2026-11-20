# Product requirements — CEO Dashboard

**Owner** — DJ, solo Laravel developer in Davao City, Philippines
**Status** — Client Sites hub, Today, and owner Analytics implemented, November 21, 2026
**Product scope** — Balanced, shipped in milestones

## Goal

Give DJ one screen for today’s client work and one reliable record of what changed on each client site. The owner-only Analytics view uses paid record totals by recorded date, outstanding record counts, tracked sites, and open and overdue follow-ups. At 10:00 AM Asia/Manila, deliver a daily brief on the dashboard and send a private notification email. Start with explicit rules and a plain template. Later, DJ can connect his own model to the same structured facts and approved application actions.

The useful signals are mail DJ marked for action, follow-ups due, and unpaid items with a due date. Start with up to five prioritized items. Show the generation time and source health so missing data can be distinguished from an empty list. Use a single-column reading order on mobile and reserve accent color for due items.

## Phase 1 — Client Sites hub

Provide login-protected owner access to clients and their sites. Each client can own multiple sites. Capture the site description, technologies, and links to the repository, hosting panel, and design files. Store URLs without embedded secrets. Store no client-site credentials.

The change-log vault records a date, title, what changed, why, and links for each entry. Display entries newest first for client walkthroughs. Payment records store the client, optional site, date, exact PHP amount, purpose, status, and invoice link. An optional due date supports later reminders. A payment can belong directly to a client and installment records can reference the same invoice.

Every client-owned record carries `client_id`. Where a record also references a site, enforce that the site belongs to the same client. Changes and payments default to internal visibility. Laravel policies and scoped queries enforce access at the server. Reserve the client role in the authorization design while owner access is the only live role.

Phase 1 uses Filament 5 for the owner panel and provisions owners through a private Laravel CLI command with a username and a hidden password prompt. The panel uses Laravel policies, and MySQL enforces client/site consistency. Audit entries store identifiers and outcomes. The owner can mark content for future client visibility, with access reserved until a portal is implemented.

**Acceptance**

- DJ can log in, create a client with multiple sites, and create, edit, and review dated changes and payments.
- Unauthenticated users cannot read records. Authorization tests cover the intended client-role boundary, including cross-client access and direct access to internal records.
- Validation rejects cross-client site associations and invalid link, amount, status, and date values. Sensitive changes and exports produce audit events without retaining secret payloads.
- Deployment serves only the public entry point and assets over HTTPS. Client login, site monitoring, Gmail, and model integration enter later phases.

## Phase 2 — Today and daily brief

The local Today page lists up to five items. Open client follow-ups due by the current Manila date come first, then due unpaid or partially paid records. Each group sorts oldest due date first. Each row opens the exact record. An open follow-up has a dated title, a client, and an optional site. Next, verify which Google account receives the own-domain mail and use a label such as `Needs action`. Use offline OAuth against that account, beginning with `gmail.metadata` if label filtering suffices. External Testing grants for Gmail expire after seven days. Prefer Google Personal mode for one mailbox where eligible, and provide an owner-only reconnect path. For third-party POP fetching, verify forwarding as the durable intake path.

At **10:00 AM Asia/Manila (02:00 UTC)**, hPanel starts a bounded PHP CLI run. Laravel claims the owner-and-date slot, reads local due items, and fetches a capped set of labeled mail references. The brief builder ranks structured facts, renders the template, and persists the dashboard brief. A separate authenticated mail transport sends a generic notification and login-protected link to DJ’s fixed address. Gmail OAuth remains read-only. Avoid persisting raw message bodies and attachments. Set retention for any Gmail-derived references.

**Acceptance**

- The brief runs daily, including weekends, and can be rerun by the owner. Each owner and Manila date has one canonical brief. A rerun reuses saved output and prevents routine duplicate sends.
- If Gmail is unavailable, show local items and a clear source-failure state. Store generation and delivery status separately. Surface ambiguous SMTP outcomes for review before resending.
- Verify the full production path on Hostinger, including time, dashboard entry, delivery to the real inbox, and a failed-connector run.

## Phase 3 — Approved actions and model trial

Use small typed Laravel operations. Start with read operations such as `list_emails` and `get_project_status`. First ship `create_task` as a rule-based proposal that DJ reviews and approves. A future `draft_reply` produces an in-app preview. Creating a Gmail draft or sending externally requires its own decision, scope review, and approval.

Keep a provider-agnostic adapter at the structured-input and validated-output boundary. DJ’s model can generate prose or propose allowed actions. Laravel owns scoping, validation, authorization, execution, and auditing. Treat email as untrusted input. An approval binds the actor, target, and reviewed arguments. Before execution, recheck permissions, validity, and record state. Use one execution key to prevent repeats. The configured daily notification is a standing rule with a fixed recipient. Run GPU or persistent inference outside shared hosting and review Google Limited Use before passing Gmail-derived data to a model.

## Phase 4+ — Client portal

Pilot with one client before broad rollout. Support named client contacts, explicitly shared site status and invoices, and client-scoped tickets. Enforce visibility in lists, detail pages, exports, and downloads. Explicitly approve fields and links before exposure, including historical entries and invoice URLs that can grant access. Test same-client access, cross-client denial, and internal-record denial through direct URLs and downloads. Decide ticket visibility and contact membership rules before inviting the pilot client.

## Architecture and hosting

One Laravel application and one MySQL database. Phase 1 uses Filament for the owner forms and tables. Phase 2 adds a custom Today page. Verify the target Hostinger PHP CLI, extensions, Composer, safe migration access, and deployment layout before release there. Use `users`, `clients`, `sites`, `site_changes`, `payments`, and `audit_events` in Phase 1. Add `follow_ups`, `connector_accounts`, and `daily_briefs` in Phase 2, `action_proposals` in Phase 3, and `client_memberships` and `tickets` in Phase 4. Index foreign keys and frequently filtered columns. Paginate lists, defaulting to 20 per page. Use UTC timestamps with Asia/Manila for business dates and displays, and format amounts in Philippine pesos.

Assume Hostinger Web or Cloud, hPanel cron, and no root or long-running workers. Use short CLI jobs with bounded connector calls and timeouts. Verify the account’s memory, process, and runtime limits. If Composer is unavailable, build compatible artifacts locally or in CI and confirm a private migration path. Register the exact HTTPS OAuth callback, validate OAuth state, and test outbound mail on an allowed SMTP port or via HTTPS.

## Security and release gates

- Require authentication, server-side authorization, CSRF protection, input validation, secure session cookies, and HTTPS. Apply least privilege to Google grants and application roles.
- Encrypt OAuth tokens and persisted sensitive personal data with AES-256-GCM. Verify the actual cipher, keep encryption keys outside Git, and plan for secure backup and key rotation.
- Audit sensitive create, update, delete, export, and approved-action operations. Record actor, target, operation, outcome, and time. Exclude tokens, credentials, and private content from logs and audit payloads.
- Keep the repository public and publish only source, public-safe planning notes, and synthetic fixtures. Keep Hostinger settings and runtime secrets private. Before a milestone push, review staged changes for secrets and real records, run lint, applicable type checks, and focused security and feature tests, then commit and push to the agreed GitHub branch. Confirm branch-to-deployment behavior separately.

## Roadmap and scope limits

| Phase | Usable release | Focused solo days |
| --- | --- | ---: |
| 1 | Owner-only client hub, change-log vault, payment records, and audit | 4–6 |
| 2 | Today page and daily dashboard/email brief | 4–7 |
| 3 | Approved internal action, followed by a model trial | 3–5, plus 2–4 for the trial |
| 4+ | Pilot contact access, invoices, status, and tickets | 7–12 |

Use dated follow-ups before adding a project board. Consider calendar, externally monitored uptime and SSL, or GitHub only when each supplies a recurring decision. Invoice issuance, financial projections, quick notes, job applications, the focus timer, and ministry workflows are outside the agreed release. Provider setup and hosting checks can add elapsed time.

## References

- [Hostinger cron](https://support.hostinger.com/en/articles/1583465-how-to-set-up-a-cron-job-at-hostinger)
- [Hostinger plan limits](https://www.hostinger.com/support/6976044-parameters-and-limits-of-hosting-plans-in-hostinger/)
- [Google OAuth audiences](https://support.google.com/cloud/answer/15549945?hl=en)
- [Gmail API scopes](https://developers.google.com/workspace/gmail/api/auth/scopes)
- [Gmail third-party mailbox changes](https://support.google.com/mail/answer/17101213)
- [Google Workspace data policy](https://developers.google.com/workspace/workspace-api-user-data-developer-policy)
- [Laravel authorization](https://laravel.com/docs/authorization)
