# Email Update - May 11, 2026

This document summarizes all email-related work completed today:

1. Delivery hardening pass across app email flows
2. New admin CLI resend workflow for part delivery tokens
3. Suffix-based token lookup/listing improvements for CLI resend

---

## 1) Delivery hardening pass

### Why this was needed

The app could send mail successfully, but delivery was inconsistent for some recipients. The largest risk was sender-domain alignment:

- Part delivery emails accepted user-provided From addresses.
- If that From domain differed from your authenticated/sending domain, SPF/DMARC alignment could fail at receiver side.
- Result: intermittent rejects, spam placement, or silent filtering.

### What was changed

#### A. Centralized send helper

Added a shared mail helper in:

- src/includes/functions.php

Key functions added:

- f_mailHeaderSafe
- f_mailDomainFromAddress
- f_mailMessageIdDomain
- f_sendEmail

#### B. Unified all email send paths to use helper

Replaced ad-hoc mail() header assembly with f_sendEmail in:

- src/includes/sound.php (part delivery modal emails)
- src/includes/email_verification.php (registration verification)
- src/includes/password_reset.php (password reset)

#### C. UI handling for reset email failure

Added missing feedback state in:

- src/login_reset.php

New state handled:

- reset=email_error

### Hardening behavior now

1. From alignment safety:
   - Uses ORGMAIL as sender of record.
   - If a user-supplied From is from another domain, that address is moved to Reply-To rather than used as envelope/sender identity.

2. Envelope sender:
   - Sends mail() with additional parameter -f <from> to improve bounce-path consistency.

3. Header quality:
   - Consistent Date, Message-ID, MIME-Version, UTF-8 content type, transfer encoding.

4. Injection safety:
   - Header-sensitive values are stripped of CR/LF before use.

5. Better logging:
   - Centralized success/failure logs include context and actor.

### Why this is better

- Reduces SPF/DMARC alignment failures from domain mismatch.
- Makes email behavior consistent across verification/reset/distribution flows.
- Improves forensic debugging when a provider rejects or delays messages.
- Keeps existing app behavior largely intact (no UI redesign required).

---

## 2) New admin resend script for part delivery

Created:

- scripts/parts_resender.php

This script lets an admin/librarian resend part-delivery emails from shell using existing token context or direct playgram/section/zip inputs.

### Core capabilities

1. Token mode:
   - Provide full token with --token.
   - Default behavior creates a NEW token from old context and sends that link.
   - Optional --reuse-token sends with the same token (only if unused and unexpired).

2. Direct mode:
   - Provide --playgram-id, --section-id, --zip-filename.
   - Script creates a new token and sends link.

3. Permissions:
   - Requires --admin-user.
   - User must have administrator or librarian in roles.

4. Template consistency:
   - Uses config/download-contract.html for message body.

5. Delivery consistency:
   - Uses shared f_sendEmail helper for the same hardened send behavior as UI.

6. Metadata update:
   - Updates download_tokens.email after successful send.

### Basic command examples

List help:

```bash
php scripts/parts_resender.php --help
```

Resend from full token (new token by default):

```bash
php scripts/parts_resender.php \
  --admin-user=admin \
  --email=musician@example.com \
  --token=0123456789abcdef0123456789abcdef
```

Resend and reuse existing token:

```bash
php scripts/parts_resender.php \
  --admin-user=admin \
  --email=musician@example.com \
  --token=0123456789abcdef0123456789abcdef \
  --reuse-token
```

Direct mode:

```bash
php scripts/parts_resender.php \
  --admin-user=admin \
  --email=musician@example.com \
  --playgram-id=3 \
  --section-id=7 \
  --zip-filename=Summer_Program_2026_Percussion_Parts.zip
```

Dry run:

```bash
php scripts/parts_resender.php \
  --admin-user=admin \
  --email=musician@example.com \
  --token=0123456789abcdef0123456789abcdef \
  --dry-run
```

---

## 3) Token suffix lookup and listing improvements

Because UI reports show only token tail (for example: ...d38b00c7), the resend script was enhanced.

### Added options

- --token-tail=HEX
- --token-end=HEX (alias)
- --list-matches
- --match-email=EMAIL
- --match-created-by=USER
- --zip-filename=FILE.zip (also usable as narrowing filter with suffix)
- --pick-latest
- --limit=N

### Behavior

1. Listing mode:
   - Show token matches without sending.

2. Send mode with suffix:
   - Resolve suffix to full token.
   - If multiple matches exist, script prints candidates and stops unless --pick-latest is provided.

3. Safer operator workflow:
   - Allows shell resend using the same partial token visible in UI.
   - Supports narrowing by recipient, sender, and zip filename.

### Suffix examples

List all matches for a tail:

```bash
php scripts/parts_resender.php \
  --admin-user=jarredprejean \
  --token-tail=d38b00c7 \
  --list-matches
```

List narrowed matches:

```bash
php scripts/parts_resender.php \
  --admin-user=jarredprejean \
  --token-tail=d38b00c7 \
  --list-matches \
  --zip-filename=Summer_Program_2026_Percussion_Parts.zip \
  --match-email=sethn@sbcglobal.net \
  --match-created-by=jarredprejean
```

Send using suffix (new token default):

```bash
php scripts/parts_resender.php \
  --admin-user=jarredprejean \
  --email=sethn@sbcglobal.net \
  --token-tail=d38b00c7 \
  --zip-filename=Summer_Program_2026_Percussion_Parts.zip \
  --match-email=sethn@sbcglobal.net \
  --match-created-by=jarredprejean
```

---

## Operational notes

1. DNS/auth remains important:
   - Keep SPF, DKIM, and DMARC aligned with ORGMAIL domain.

2. Current app architecture:
   - Still uses native PHP mail(), now hardened and consistent.
   - Future optional upgrade: SMTP/provider transport (PHPMailer/API) for stronger deliverability controls.

3. Monitoring:
   - Review application logs for f_sendEmail context entries:
     - sound.php
     - password_reset.php
     - email_verification.php
     - scripts/parts_resender.php

---

## Testing & Validation (May 13, 2026)

Initial testing of the delivery hardening changes has been completed:

- **Part delivery emails**: Successfully sent and received via Gmail.
  - Previously: Parts delivery was inconsistent for some recipients, particularly Gmail users.
  - Now: Email arrived successfully with proper SPF/DMARC alignment.
- **CLI resend script**: Tested and working as expected.
- **Email consistency**: New shared mail helper is functioning correctly across all send paths.

**Status**: Awaiting feedback from other users before declaring full success. Initial testing validates the domain alignment and delivery hardening strategy.

---

## Files changed during this work

- src/includes/functions.php
- src/includes/sound.php
- src/includes/email_verification.php
- src/includes/password_reset.php
- src/login_reset.php
- scripts/parts_resender.php
- EMAIL_UPDATE.md
