# Email Verification System for User Registration

This system implements email verification for new user registrations by extending the existing password reset functionality. Instead of creating a separate table, it reuses the existing `password_reset` table with additional columns.

## Files Added/Modified

### New Files Created:
1. `setup/email_verification_table.sql` - Database modifications (adds columns to existing table)
2. `includes/email_verification.php` - Handles verification email sending
3. `verify_email.php` - Email verification landing page
4. `cleanup_verification.php` - Enhanced cleanup script for both password reset and email verification tokens
5. `admin_verifications.php` - Admin page to view both pending verifications and password resets

### Files Modified:
1. `register.php` - Updated to use email verification workflow

## Database Changes

The system extends the existing `password_reset` table instead of creating a new one. Run the SQL in `setup/email_verification_table.sql`:

```sql
-- Modify existing password_reset table to support email verification
ALTER TABLE `password_reset` 
ADD COLUMN `username` varchar(128) DEFAULT NULL COMMENT 'Username for email verification requests',
ADD COLUMN `name` varchar(255) DEFAULT NULL COMMENT 'Full name for email verification requests',
ADD COLUMN `password_hash` varchar(128) DEFAULT NULL COMMENT 'Hashed password for email verification requests',
ADD COLUMN `request_type` enum('password_reset', 'email_verification') DEFAULT 'password_reset' COMMENT 'Type of request';

-- Add index for better performance
ALTER TABLE `password_reset` 
ADD INDEX `idx_request_type` (`request_type`);
```

## How It Works

1. **User Registration**: When a user submits the registration form, instead of immediately creating an account, the system:
   - Validates the form data
   - Stores the user information temporarily in the `password_reset` table with `request_type='email_verification'`
   - Generates a secure verification token (same method as password reset)
   - Sends an email with a verification link

2. **Email Verification**: When the user clicks the verification link:
   - The system validates the token and checks if it hasn't expired (1 hour)
   - If valid, creates the user account in the `users` table
   - Removes the temporary verification record from `password_reset` table
   - Shows a success message

3. **Security Features**:
   - Tokens expire after 1 hour
   - Prevents duplicate registrations during verification period
   - Uses the same secure token system as password reset
   - Validates both username and email uniqueness
   - Leverages proven password reset infrastructure

## Advantages of Shared Table Approach

- **Single table** manages both password resets and email verifications
- **Unified cleanup** - one script handles both types of expired tokens
- **Shared infrastructure** - same security, token generation, and expiration logic
- **Consistent administration** - view both request types in one admin interface
- **Database efficiency** - extends existing table instead of creating duplicate structure

## Maintenance

### Cleanup Script
Run `cleanup_verification.php` periodically (recommend daily via cron job) to remove expired tokens of both types:

```bash
# Add to crontab to run daily at 2 AM
0 2 * * * /usr/bin/php /path/to/musicLibraryDB/includes/cleanup_verification.php
```

This single script now handles cleanup for both password reset tokens and email verification tokens.

### Monitoring
- Use `admin_verifications.php` to view both pending email verifications and password resets in one interface
- Check logs for verification-related activity
- Monitor the `password_reset` table with queries filtered by `request_type`

## Configuration

The system uses existing email configuration from your `includes/config.php`:
- `ORGNAME` - Organization name used in emails
- `ORGMAIL` - From email address
- `ORGHOME` - Base URL for verification links

## Database Queries for Monitoring

### View pending email verifications:
```sql
SELECT * FROM password_reset 
WHERE request_type = 'email_verification' 
AND password_reset_expires >= UNIX_TIMESTAMP();
```

### View pending password resets:
```sql
SELECT * FROM password_reset 
WHERE request_type = 'password_reset' 
AND password_reset_expires >= UNIX_TIMESTAMP();
```

### Clean up expired tokens manually:
```sql
DELETE FROM password_reset 
WHERE password_reset_expires < UNIX_TIMESTAMP();
```

## User Experience

1. User fills out registration form
2. Receives message: "Registration submitted! Please check your email for a verification link"
3. Checks email and clicks verification link
4. Sees success page and can log in
5. If link expires, user must register again

## Error Handling

The system handles various error conditions:
- Expired verification links
- Invalid tokens
- Username taken during verification period
- Email sending failures
- Database errors

## Security Considerations

- Verification tokens are cryptographically secure (same as password reset)
- Email addresses and usernames are checked for duplicates in both `users` table and pending `password_reset` entries
- Failed verifications don't reveal system information
- Tokens are hashed in the database using the same method as password reset
- Expired tokens are automatically cleaned up by the same process
- Uses proven security model from existing password reset functionality
- The `request_type` field ensures separation between password reset and email verification workflows
