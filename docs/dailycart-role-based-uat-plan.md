# DailyCart Role-Based User Acceptance Test Plan

Document owner: DailyCart product team  
Applies to: Customer web/mobile, Vendor web/mobile, Rider web/mobile, Admin, Super Admin, and finance operations  
Environment: Isolated staging only  
Production-data rule: Never use, copy, reset, truncate, seed, or modify the production database for UAT.

## 1. Purpose

This plan verifies that representative DailyCart users can complete their real business tasks safely and without assistance. It complements automated tests and the mobile regression checklist; it does not replace security, performance, or integration testing.

UAT must capture:

- Whether each task was completed successfully.
- Actual completion time.
- Confusing labels, navigation, status messages, or next steps.
- Functional and visual defects.
- Evidence such as screenshots, order numbers, notification timestamps, and sanitized logs.
- Participant and product-owner sign-off.

## 2. Scope

### In scope

- Authentication, authorization, profile, and role-specific navigation.
- Product/store discovery, cart, checkout, payment, tracking, delivery, and rating.
- Vendor catalog, order fulfilment, earnings, refunds, and payouts.
- Rider assignment, active-delivery workflow, background location, offline recovery, proof, earnings, and ratings.
- Admin approvals, order operations, delivery management, support, notifications, reports, finance, refunds, payouts, and auditability.
- Cross-role notifications, email delivery, invoice access, and notification deep links.
- Android validation for all three role apps and iOS validation on signed physical-device builds when available.
- Accessibility, responsive layout, loading/empty/error states, and offline/reconnect behaviour.

### Out of scope

- Production transactions or production customer information.
- Destructive database operations.
- Penetration testing and capacity testing; these use separate plans.
- Live payment settlement unless a payment-provider sandbox is explicitly configured.

## 3. Roles and participants

Recruit at least one representative participant per row. A participant should not test features they implemented.

| Participant | Required experience | Primary surfaces | Sign-off responsibility |
| --- | --- | --- | --- |
| Customer | Regular online shopper | Customer web, Android, iOS where available | Shopping, checkout, tracking, support, rating |
| Vendor | Store owner or fulfilment operator | Vendor web and mobile | Catalog, orders, refunds, earnings, payouts |
| Rider | Delivery rider familiar with Android navigation | Rider mobile and rider web | Assignment, location sharing, delivery lifecycle, earnings |
| Admin | Operations/support employee | Admin web | Approvals, orders, support, moderation, operational recovery |
| Super Admin | Platform owner or delegated lead | Super Admin and Admin web | Permissions, delivery rules, escalations, final operational sign-off |
| Finance | Finance or reconciliation employee using an Admin/Super Admin account with the required permissions | Finance reports, payments, refunds, payouts, exports | Financial accuracy and reconciliation |

`Finance` is a UAT participant persona, not a new DailyCart application role. It must use the least-privileged existing Admin/Super Admin account permitted by the current RBAC design.

## 4. Entry criteria

Do not start formal UAT until all conditions are true:

1. The release candidate is deployed to an isolated staging environment.
2. Staging uses a separate database, queue, cache, mail sandbox, storage path, and payment sandbox configuration.
3. Required additive migrations have been applied to staging and verified; production is untouched.
4. Queue worker, scheduler, broadcasting/polling fallback, email sandbox, maps, and Firebase test configuration are running.
5. Customer, approved/pending vendor, approved/pending rider, Admin, and Super Admin test accounts exist.
6. Test products, stock, coupons, delivery zones, fees, addresses, payment methods, and clean order scenarios are available.
7. Android release-candidate builds are installed on supported physical devices.
8. iOS tests are run on a signed build and physical iPhone when macOS/Xcode/signing are available; otherwise mark them `Blocked`, never `Passed`.
9. Automated critical-path tests pass and no unresolved blocker is known.
10. Participants have the test script, evidence location, privacy guidance, and defect-reporting instructions.

## 5. Test data and safety

- Use invented names, addresses, phone numbers, and sandbox email inboxes.
- Do not paste passwords, access tokens, card details, secrets, or personal information into evidence.
- Use unique run identifiers such as `UAT-YYYYMMDD-R01` in subjects, support tickets, and notes.
- Use sandbox PayHere/card callbacks only. No real charge is permitted.
- Use staging-only order numbers when reporting defects.
- Never run `migrate:fresh`, `db:wipe`, destructive seeders, truncate, or database restore commands against an existing environment.
- Reset test state only through approved staging fixtures or normal application actions.

## 6. Execution method

For each case, the facilitator records the result in `docs/uat/dailycart-uat-execution-template.csv`.

Result values:

- `Pass`: expected result achieved without a material workaround.
- `Pass with observation`: completed, but a non-blocking UX concern was recorded.
- `Fail`: expected result was not achieved.
- `Blocked`: environment, dependency, or prerequisite prevented execution.
- `Not applicable`: approved as irrelevant to this release or participant.

The facilitator should observe rather than guide. If help is required, record the exact point of confusion and the assistance given.

## 7. Customer acceptance scenarios

| ID | Task | Expected acceptance result | Target time |
| --- | --- | --- | --- |
| CUS-01 | Register, verify, sign in, sign out, and sign back in | Correct role landing page; validation is clear; session/token is removed on logout | 4 min |
| CUS-02 | Browse home offers, featured/popular stores, store details, categories, and products | Content loads; empty/error states are understandable; navigation selection matches the visible screen | 4 min |
| CUS-03 | Search/filter products, open details, select quantity, and use wishlist/share | Correct results and stock; sharing opens the platform share sheet rather than only copying text | 4 min |
| CUS-04 | Build and edit a cart containing valid products | Totals, quantities, vendor grouping, fees, and stock validation are correct | 4 min |
| CUS-05 | Apply a valid and invalid coupon and review loyalty impact | Discount/loyalty explanations and resulting totals are accurate | 3 min |
| CUS-06 | Select address, delivery schedule, zone-derived fee, and payment method | Address and delivery fee are correct; invalid schedules/addresses show actionable errors | 5 min |
| CUS-07 | Submit checkout, simulate an interrupted retry using the same idempotency key, and open confirmation | Exactly one order is created; confirmation and invoice details agree with order totals | 5 min |
| CUS-08 | Complete COD and sandbox electronic-payment paths, including failed payment recovery | Payment state, retry action, notification, and order state remain consistent | 6 min |
| CUS-09 | Follow confirmed, packed, rider-assigned, accepted, picked-up, on-the-way, delivered states | Notification Action Center and order timeline show correct role-aware actions and deep links | During E2E run |
| CUS-10 | View live rider tracking, stale state, offline state, reconnect, and terminal-state shutdown | Rider/destination markers and age are clear; offline/retry state appears; refresh stops at terminal state | During E2E run |
| CUS-11 | Rate the rider with score/tags/comment and attempt a duplicate rating | Valid rating saves once; invalid tags are explained; duplicate is prevented | 3 min |
| CUS-12 | Request full and partial refunds and follow status | Amount and reason remain visible; partial refund does not mark the full order/payment refunded | 5 min |
| CUS-13 | Create/reply to a support ticket and follow assignment, status, and closure | Ticket visibility, messages, notifications, and status transitions are correct | 5 min |
| CUS-14 | Open delivered-order invoice and notification email | Invoice, email, order, payment, fees, and refund totals agree | 3 min |
| CUS-15 | Use the app offline, reconnect, and retry a safe read/action | Clear offline indicator; no frozen screen or duplicate write; recovery occurs without restart | 4 min |

## 8. Vendor acceptance scenarios

| ID | Task | Expected acceptance result | Target time |
| --- | --- | --- | --- |
| VEN-01 | Register/update vendor and store profile, then have Admin review details | Admin sees current details; approval status and notification are correct | 6 min |
| VEN-02 | Create/edit a product, images, price, category, and stock | Validation is clear; product enters the correct approval/visibility state | 6 min |
| VEN-03 | Find a new order and inspect customer-safe fulfilment information | Only vendor-owned orders/data are visible; notification deep link opens the order | 3 min |
| VEN-04 | Confirm and pack an order | Customer/Admin receive the correct timeline state and notifications once | 4 min |
| VEN-05 | Observe rider assigned, accepted, picked-up, on-the-way, and delivered | Status and active location freshness are correct; unnecessary history is not exposed | During E2E run |
| VEN-06 | Handle customer refund request and provide a response | Requested amount/reason, response, notifications, and final decision remain auditable | 5 min |
| VEN-07 | Review delivered-order earning and request a payout | Vendor sees earnings only, not customer delivery/service fees; payout request is recorded once | 4 min |
| VEN-08 | Review low stock, reviews, and rating-related notifications | Counts, links, read/unread/action filters, and empty states behave consistently | 4 min |
| VEN-09 | Test notification filters and selected-tab persistence | All/Unread/Action tabs remain visible and selected styling matches active filter | 2 min |
| VEN-10 | Lose network during a safe edit/read and reconnect | Unsaved state is handled clearly; no duplicate product/order transition occurs | 4 min |

## 9. Rider acceptance scenarios

| ID | Task | Expected acceptance result | Target time |
| --- | --- | --- | --- |
| RID-01 | Sign in as approved rider; confirm pending/rejected rider restrictions | Only approved rider reaches operational screens; logout stops active tracking | 3 min |
| RID-02 | Receive and accept an assigned delivery | Assignment appears once; correct delivery opens; tracking starts only after allowed transition | 3 min |
| RID-03 | Validate location permission education and denial/recovery | Tracking state clearly shows active, offline waiting, permission required, or stopped | 4 min |
| RID-04 | Background/lock Rider app during accepted/picked-up/on-the-way delivery | Persistent Android notification appears; valid locations continue at adaptive intervals | 10 min |
| RID-05 | Disable network, move, restore network, and inspect queue sync | Encrypted outbox persists points; offline state is visible; retry deduplicates and latest point is prioritized | 10 min |
| RID-06 | Force-stop/restart app or reboot during an active delivery where platform rules allow | Tracking recovery is policy-compliant and does not attach to another rider/delivery | 10 min |
| RID-07 | Move through accepted, picked-up, and on-the-way | Legal transitions succeed once; customer/vendor/admin timeline and notifications agree | During E2E run |
| RID-08 | Complete delivery with required proof | Delivery becomes terminal; tracking and foreground service stop immediately | 4 min |
| RID-09 | Record failed delivery with category and recovery option | Reason, resolution history, notification, SLA/escalation, and rider availability are correct | 5 min |
| RID-10 | Attempt location submission for another rider or completed delivery | Request is denied; no current/history point is stored or broadcast | 3 min |
| RID-11 | Review earnings and a customer rating; report rating if needed | Earnings match the completed delivery; rating/report/moderation status is visible | 4 min |
| RID-12 | Confirm notification Action Center and support updates | Filters persist; deep links open the right delivery/ticket; duplicate alerts are absent | 3 min |

## 10. Admin and Super Admin acceptance scenarios

| ID | Task | Expected acceptance result | Target time |
| --- | --- | --- | --- |
| ADM-01 | Review vendor/rider registration details and approve/reject | Full current profile is visible; status, permissions, and notification change correctly | 5 min |
| ADM-02 | Review customer, vendor, rider, order, and payment data as permitted | RBAC is enforced; sensitive/unrelated information is not exposed | 5 min |
| ADM-03 | Assign/reassign a rider to an eligible delivery | Old rider loses authorization/tracking; new rider and all relevant roles are notified | 5 min |
| ADM-04 | Monitor active delivery location and stale/offline state | Only relevant active location is shown with age; terminal/inactive location is hidden | 4 min |
| ADM-05 | Resolve failed delivery using retry, reassign, return-to-vendor, or refund decision | Structured reason, resolution history, notifications, owner, and SLA are recorded | 7 min |
| ADM-06 | Process full and partial refund and inspect audit trail | Legal state transitions and exact amount are preserved; totals and loyalty reversal are proportional | 6 min |
| ADM-07 | Review payout request and resulting vendor/rider records | Amount and status agree with earnings ledger; duplicate processing is prevented | 5 min |
| ADM-08 | Verify bank transfer and handle failed/refunded payment alert | Decision, actor, timestamp, payment/order state, and notifications agree | 5 min |
| ADM-09 | Moderate a reported rider rating | Decision is auditable; customer/rider visibility and notifications match policy | 4 min |
| ADM-10 | Manage support ticket assignment, reply, status, and closure | Customer/vendor/rider receives the correct update and can access only own ticket | 5 min |
| ADM-11 | Test delivery fee simulator with full address, zone, district, and province fallback | Most-specific matching rule wins and explanation/fee is correct | 5 min |
| ADM-12 | Export sales/finance reports to Excel and PDF | Files download, open, match filters/totals, and contain no unauthorized data | 5 min |
| ADM-13 | Confirm Admin versus Super Admin permission boundaries | Restricted actions are hidden/denied and permitted actions remain usable | 5 min |
| ADM-14 | Review Action Center and Smart Activity Timeline | Role-aware actions, unread counts, filters, and order/delivery events are correct | 4 min |

## 11. Finance acceptance scenarios

| ID | Task | Expected acceptance result | Target time |
| --- | --- | --- | --- |
| FIN-01 | Reconcile an order from subtotal through grand total | Subtotal, discount, delivery fee, service charge, tax if applicable, and payment amount reconcile exactly | 5 min |
| FIN-02 | Reconcile vendor earning and rider earning | Vendor earning excludes customer-facing fees and commission as designed; rider earning matches rule | 5 min |
| FIN-03 | Reconcile COD, paid, failed, bank-transfer, and refunded states | Each amount appears once in the correct category and date range | 6 min |
| FIN-04 | Verify a partial refund | Refunded and remaining paid totals are correct; payment/order are not incorrectly fully refunded | 5 min |
| FIN-05 | Verify sandbox PayHere duplicate and stale callbacks | One legal payment outcome is recorded; immutable attempts/events remain auditable | 5 min |
| FIN-06 | Review vendor/rider payout request and approval lifecycle | Requested, approved, processed, rejected, and failed amounts reconcile with ledger | 6 min |
| FIN-07 | Compare dashboard totals with Excel/PDF export for same filters | Counts, rounding, LKR formatting, date timezone, and totals match | 6 min |
| FIN-08 | Verify finance access boundaries | Persona can perform required work but cannot access unrelated Super Admin operations | 3 min |

## 12. Cross-role end-to-end journeys

These journeys must be executed as coordinated handoffs, not as isolated screens.

### E2E-01 Successful COD delivery

Customer places order -> Vendor confirms/packs -> Admin assigns Rider -> Rider accepts/picks up/goes on the way -> Customer observes live/stale tracking -> Rider delivers -> Customer receives invoice and rates Rider -> Vendor/Rider earnings and finance totals reconcile.

Acceptance: one order, one delivery lifecycle, no duplicate notifications or earnings, correct tracking shutdown, and consistent status on every role surface.

### E2E-02 Successful sandbox electronic payment

Customer pays -> valid callback is processed -> duplicate callback is replayed -> Vendor fulfils -> Rider delivers -> Finance reconciles.

Acceptance: payment is paid exactly once; a later duplicate/failed callback cannot downgrade it; invoice and ledgers agree.

### E2E-03 Partial refund

Customer requests a partial refund -> Vendor responds -> Admin/Finance approves -> Customer receives result -> all roles inspect totals.

Acceptance: only the approved amount is refunded; remaining payment/order value remains valid; audit history and notifications identify actors and timestamps.

### E2E-04 Failed delivery recovery

Rider records a structured failure -> Admin selects retry/reassignment/return/refund -> affected roles receive instructions -> SLA/escalation is observable -> resolution completes.

Acceptance: no orphaned assignment, location sharing, payment, stock, earning, or customer status remains.

### E2E-05 Connectivity loss during active delivery

Rider goes offline -> points enter encrypted queue -> Customer sees stale/offline UX -> Rider reconnects -> queued points sync -> delivery completes.

Acceptance: no duplicate history, current location does not move backward, status is understandable, and terminal tracking stops.

## 13. Non-functional acceptance checks

Apply these checks to every role where relevant:

- Layout works at supported web widths and on target phone sizes without clipped controls.
- Text scaling and screen-reader labels keep primary tasks usable.
- Focus order, keyboard access, contrast, tap targets, and validation messages are accessible.
- Loading, empty, error, offline, stale, and retry states are distinguishable.
- Back navigation and bottom/side navigation accurately reflect the visible destination.
- Monetary values use consistent LKR formatting and Asia/Colombo dates/times are understandable.
- Double-taps, refreshes, timeouts, and reconnects do not create duplicate writes.
- Private customer, payment, support, and location data are visible only to authorized roles.

## 14. Defect priority and release decision

| Severity | Definition | Release treatment |
| --- | --- | --- |
| S1 Blocker | Data loss/corruption, security/privacy breach, wrong financial result, duplicate charge/order, unusable critical journey | Must be fixed and retested before release |
| S2 Critical | Critical role cannot complete checkout, fulfilment, delivery, refund, payout, or approval without a safe workaround | Must be fixed and retested before release |
| S3 Major | Important feature fails but a documented safe workaround exists | Product owner must approve deferral |
| S4 Minor | Cosmetic/content/usability issue with no incorrect data or blocked task | May be scheduled with owner and target release |

Every defect must include run ID, case ID, role, platform/build, prerequisites, exact steps, expected/actual result, severity, sanitized evidence, and retest result.

## 15. Exit criteria

UAT passes only when:

1. All P0/P1 business journeys and every S1/S2-related case pass on the release candidate.
2. No unresolved S1 or S2 defect remains.
3. At least 95% of applicable cases pass; all failures have an owner and approved disposition.
4. Customer, Vendor, Rider, Admin/Super Admin, and Finance participants complete and sign their assigned cases.
5. Finance confirms order, payment, refund, earning, payout, and export reconciliation.
6. Android physical-device tests pass for customer, vendor, and rider builds.
7. iOS physical-device results are recorded separately; an unavailable iOS environment is reported as a release risk, not silently treated as passing.
8. Notification/email/invoice evidence exists for at least one complete successful order and each exceptional financial path.
9. Product owner accepts recorded usability observations, completion times, and deferred defects.

## 16. Sign-off

| Area | Participant | Result | Date | Signature/approval reference | Open risks |
| --- | --- | --- | --- | --- | --- |
| Customer |  |  |  |  |  |
| Vendor |  |  |  |  |  |
| Rider |  |  |  |  |  |
| Admin operations |  |  |  |  |  |
| Super Admin |  |  |  |  |  |
| Finance |  |  |  |  |  |
| Product owner final decision |  |  |  |  |  |

Final decision must be one of: `Accepted`, `Accepted with documented risks`, or `Rejected`.

