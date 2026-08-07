# Part Distribution 2.0 Plan

## Current Decision (July 2026)

No software rewrite is required right now.

The current AllanaCrusis distribution tooling remains in service as-is, while operations shift to a Drupal-first publishing model for member access.

## Overview

This document captures a practical operating model that preserves the current working AllanaCrusis flow while moving day-to-day distribution into Drupal private files by section.

Primary goals:
- Keep existing software stable and unchanged in the short term.
- Publish section materials into Drupal private files instead of email-based delivery.
- Ensure members receive access by section assignment at login.
- Lower librarian support load by using persistent section destinations.

## Drupal-First Operating Workflow (No Rewrite)

1. Maintain section definitions in AllanaCrusis as the source of section structure.
2. Mirror those sections in Drupal groups (one group per section).
3. Publish files to Drupal private file locations mapped to each section/group.
4. Assign member accounts to section groups at onboarding/login administration.
5. Members authenticate in Drupal and see files already available for their section.
6. Use current AllanaCrusis email/token workflow only as fallback or exception handling.

## Validated Drupal State (acwe-dev)

The Drupal dev site already contains most of the membership plumbing needed for this model, without rewriting software.

Confirmed:
- Group module is enabled, with two group types: `section` and `members`.
- Section groups exist and match instrument/section naming.
- Registration approval logic auto-adds users to a section group based on `field_primary_section`.
- Registration approval logic also adds users to a persistent `Members` group.

Verified section mapping currently in use:

| Section key | Group ID | Group label |
|---|---:|---|
| conductor | 1 | Conductor |
| flutes | 2 | Flutes/Piccolos |
| doublereeds | 3 | Oboes/Bassoons/English Horns |
| highclarinets | 4 | Clarinets |
| lowclarinets | 5 | Bass Clarinets |
| saxophones | 6 | Saxophones |
| horns | 7 | French Horns |
| trumpets | 8 | Cornets/Trumpets |
| trombones | 9 | Trombones |
| lowbrass | 10 | Euphoniums/Tubas |
| percussion | 11 | Percussion |

Members groups:
- Group ID 12: Announcements
- Group ID 13: Members

Important current gaps for private-file publishing:
- Exported file config is still public-default (`default_scheme: public`).
- Private directory exists on disk, but private scheme usage is not yet reflected in exported field storage.
- Section group relationships are currently configured for section membership plus section blog/forum content, not a dedicated section document content type.

Operational implication:
- Keep software as-is.
- Complete the final Drupal admin/config steps so uploaded distribution files are private and attached to section-scoped content.

## What Changes vs. What Stays

Changes now:
- Operational process: librarians publish into Drupal private files.
- Access model: section-based group membership in Drupal.

Stays the same now:
- Existing AllanaCrusis software behavior and code paths.
- Existing token/link mechanisms for fallback scenarios.
- Existing reporting/resend tooling for exceptions.

## Minimal Implementation Steps (Operations)

1. Confirm canonical section list and naming conventions.
2. Create/verify matching Drupal groups for each section.
3. Define private file directory convention per section.
4. Publish current cycle materials into the correct private directories.
5. Validate 2-3 member logins per section to confirm visibility/access.
6. Document exception flow: when to use legacy email links.

## Acceptance Criteria

- A section member can log in and access section files without needing an emailed token link.
- A non-member cannot access another section's private files.
- Librarian can publish once to Drupal private files and avoid per-recipient send workflows for standard releases.
- Legacy email/token delivery remains available for edge cases.

## Problem Statement

Current one-time expiring token links are secure enough for many cases, but operational issues remain:
- Recipients open links late and tokens expire.
- Recipients download on phones when they intended desktop.
- Some users struggle with file location and transfer after mobile download.
- Support overhead appears for preventable workflow mistakes.

This is often a human-process challenge, not a core software defect.

## Principles

1. Identity proof and usability must be balanced.
2. Email-only improvements should be additive and reversible.
3. Cookie memory can reduce friction but cannot be sole identity proof.
4. Any temporary mechanism should ease migration to Drupal identity.

## Current Baseline (Today)

- One-time tokenized links.
- Expiration controlled by `DOWNLOAD_TOKEN_EXPIRY_DAYS`.
- Intermediate confirmation page.
- Token reporting and cleanup.
- Resend tooling for admin/librarian support.

## Option Catalog (Pre-Drupal)

### A. Email-Only Mechanisms

1. Two-message handshake
- Message 1: request/open link.
- Message 2: actual download link after first click.
- Benefit: confirms mailbox access and reduces stale-forwarded link risk.

2. Email OTP gate (no account)
- Link opens verification page and sends a 6-digit code to same address.
- User enters code to unlock download.
- Suggested defaults: 10-minute expiry, 5 attempts, 30-60 second resend cooldown.

3. Magic-link challenge with masked address check
- User must confirm part of recipient address shown as masked value.
- Lightweight anti-forwarding friction.

4. Split secret delivery
- Link sent in one message, short passcode sent separately.
- Raises effort for unauthorized use of a single compromised message.

5. Reply-to-release flow
- Recipient replies "YES" to trigger a fresh link.
- Useful for less technical users and precise timing control.

6. Fresh-link button
- Existing email includes "Send me a fresh link" action to same address.
- Reduces support requests for expired tokens.

7. Bounded reuse window
- Instead of strict one-time, allow 2-3 downloads within 24-48 hours.
- Reduces accidental lockout after first mobile download.

8. Expiry reminder cadence
- Automated "expires soon" notifications prior to token expiration.
- Improves completion rates.

### B. Device and UX Mechanisms

1. Device-aware handoff page (recommended)
- Before final download, present:
  - Download on this device
  - Send link to desktop email
  - Show QR for another device
  - Copy desktop-safe link

2. Mobile warning banner
- Detect likely phone user agent and warn where ZIP will be saved.
- Encourage "Send to desktop" for non-technical users.

3. One-click desktop resend from confirmation page
- Self-service recovery without librarian intervention.

4. Persistent short-term availability
- Keep package accessible for 48-72 hours after verified open.
- Reduces panic/support from wrong-device downloads.

### C. Security and Audit Enhancements

1. Recipient-bound grants
- Verify recipient email before issuing short-lived download grant.

2. Trusted-device cookie (secondary control)
- Signed, secure cookie to reduce repeat OTP friction.
- Not a standalone proof of identity.

3. Step-up verification rules
- Trigger OTP on first use, new device, or unusual behavior.

4. Download audit trail
- Log open, verify, download, resend, fail events with timestamp/IP/user-agent.

5. Optional per-recipient watermarking
- Add user marker to ZIP manifest or files where policy allows.

## Recommended Transitional Architecture

### Phase 1: Immediate (Low Risk, 1-2 weeks)

- Keep existing token flow.
- Add device-aware handoff page and mobile warning copy.
- Add one-click self-service "send to desktop".
- Change from one-time strict to bounded reuse (2-3 downloads, 48 hours).
- Add expiry reminder emails.

Expected outcomes:
- Fewer wrong-device incidents.
- Fewer support tickets for accidental first-use lockout.
- Better recipient completion rates.

### Phase 2: Stronger Recipient Assurance (2-4 weeks)

- Add email OTP gate for first verified download event.
- Introduce trusted-device cookie for short repeat access window.
- Keep fresh-link self-service path.

Expected outcomes:
- Better assurance of intended-recipient access.
- Controlled friction only when risk is higher.

### Phase 3: Drupal-First Adoption (4-12 weeks)

- Make Drupal private section pages the primary destination in all outbound messages.
- Keep legacy email-link flow as fallback only.
- Auto-provision member accounts and promote passwordless first-login links.
- Enforce role-based section access in Drupal.

Expected outcomes:
- Identity-centered access model.
- Persistent per-section download areas.
- Major reduction in token lifecycle edge cases.

### Phase 4: Drupal-Centric End State

- Retire direct file-link distribution except emergency fallback.
- All downloads occur in authenticated Drupal section portals.
- Keep audit, device memory, and step-up verification for sensitive actions.

## Operational Playbooks

### Librarian Send Playbook

1. Send close to rehearsal/use window.
2. Include explicit "open on device where you want files saved" guidance.
3. Use one fresh link per recipient.
4. Monitor token/download report after distribution.
5. Trigger proactive resend when link remains unused near expiry.

### Recipient Recovery Playbook

1. If link expired: choose "Send me a fresh link".
2. If downloaded on phone by mistake: choose "Send to desktop".
3. If prompted for OTP: enter 6-digit code from email.
4. If still blocked: contact librarian with section and concert name.

## Metrics and KPIs

Track monthly:
- Expired-before-first-download rate.
- Resend-to-success rate.
- Mobile-first error rate (downloaded on phone, then needed recovery).
- Median time from send to successful download.
- Repeat-support-recipient rate.
- Drupal adoption rate by section.

## Decision Matrix

### Option Weighting (suggested)
- Security assurance: 35%
- User success rate: 30%
- Support burden: 20%
- Implementation effort: 15%

### Quick Comparison

- One-time token only:
  - Security: medium
  - Usability: medium-low
  - Support burden: medium-high
  - Effort: low

- Token + device-aware handoff + bounded reuse:
  - Security: medium
  - Usability: high
  - Support burden: low-medium
  - Effort: low-medium

- Token + OTP + trusted device + handoff:
  - Security: high
  - Usability: medium-high
  - Support burden: low
  - Effort: medium

- Drupal private section portals:
  - Security: high
  - Usability: high (after onboarding)
  - Support burden: low
  - Effort: medium-high

## Risks and Mitigations

1. Added UX complexity
- Mitigation: progressive rollout; keep default path simple.

2. Email deliverability for OTP/reminders
- Mitigation: monitor bounce/spam rates; keep sender reputation strong.

3. User resistance to change
- Mitigation: section-captain onboarding and short tutorial copy.

4. Parallel-system confusion during transition
- Mitigation: clear timeline and deprecation milestones.

## Implementation Roadmap (90 Days)

Days 1-14
- Deploy device-aware handoff, mobile warning, and desktop resend.
- Enable bounded reuse policy.
- Add expiry reminder email.

Days 15-35
- Deploy OTP gate for first-use/new-device scenarios.
- Add trusted-device cookie logic and policy.
- Instrument audit events.

Days 36-70
- Launch Drupal-first links for pilot sections.
- Auto-provision accounts for pilot members.
- Run communication and support scripts.

Days 71-90
- Expand Drupal-first to all sections.
- Keep fallback email-link path for exceptions.
- Review KPIs and set cutoff for legacy direct-link method.

## Communications Plan

### Message Themes

- "Use the device where you want your files stored."
- "Need desktop? One click sends it there."
- "Your section portal in Drupal is now your permanent home for parts."

### Cadence

1. Pre-change notice (1 week before each phase).
2. Go-live day reminder.
3. 1-week follow-up with quick tips.
4. Monthly status update with adoption metrics.

## Recommendation

Adopt a staged hybrid strategy:
1. Immediately fix device-flow pain (handoff + desktop resend + bounded reuse).
2. Add OTP as step-up assurance for recipient identity.
3. Migrate to Drupal private section areas as primary distribution destination.

This path provides near-term relief for real user behavior while steadily moving to the long-term identity and access model.

---

Last Updated: July 21, 2026
Document Version: 2.0
Owner: Library Administration / Platform Team
