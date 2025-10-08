# Security Features

This bundle implements several security best practices to protect your 2FA implementation.

## CSRF Protection

**As of version 6.4.0**: All state-changing operations now require valid CSRF tokens to prevent Cross-Site Request Forgery attacks.

### Protected Operations

The following operations are protected with CSRF tokens:
- Enable/Disable 2FA
- Reset 2FA (delete shared secret)
- Clear trusted devices
- Admin operations (disable/reset 2FA for other users)
- Forgot 2FA verification

### Implementation

All forms use Symfony's `#[IsCsrfTokenValid()]` attribute for automatic CSRF validation. All actions have been converted from GET to POST requests for security compliance.

**Important:** If you're integrating with this bundle's controllers, ensure your forms include CSRF tokens:

```twig
<form method="post" action="{{ path('svc_totp_disable') }}">
    <input type="hidden" name="_csrf_token" value="{{ csrf_token('totp_disable') }}">
    <button type="submit">Disable 2FA</button>
</form>
```

### CSRF Token IDs

The following CSRF token IDs are used throughout the bundle:
- `totp_enable` - Enable 2FA
- `totp_disable` - Disable 2FA
- `totp_reset` - Reset 2FA
- `totp_clear_td` - Clear trusted devices
- `totp_admin_disable` - Admin disable 2FA
- `totp_admin_reset` - Admin reset 2FA
- `totp_admin_clear_td` - Admin clear trusted devices
- `totp_forgot_verify` - Forgot 2FA verification

## Input Validation

### User ID Validation

The bundle validates all user ID inputs to prevent type errors and invalid requests:
- User IDs must be numeric
- User IDs must be positive integers
- Invalid user IDs return appropriate error messages

### Email Validation

The "Forgot 2FA" feature includes comprehensive email validation:
- Email format validation using PHP's `FILTER_VALIDATE_EMAIL`
- Whitespace trimming
- Empty/null value detection
- Configuration-time validation (prevents invalid configuration)

## Null Safety

All `getUser()` calls include proper `instanceof User` checks to prevent null pointer exceptions. If a user is not authenticated, the bundle returns an `AccessDeniedException` with appropriate error messages.

## Error Handling

### User-Facing Error Messages

Error messages are clear and actionable:
- "Cannot enable 2FA. Please scan the QR code first." (instead of generic "Cannot enable 2FA")
- Validation errors include specific details about what went wrong

### Exception Logging

**As of version 6.4.0**: The logger now logs exceptions to PHP's error log even in production environments before swallowing them. This enables debugging in production without exposing stack traces to users.

```php
// Example error log entry
[SvcTotpBundle] Logger exception: ArgumentCountError in /path/to/TotpLogger.php:28 - Message: Too few arguments
```

This feature helps administrators identify issues without compromising security.

## Confirmation Dialogs

All destructive actions (disable/reset 2FA, clear trusted devices) now include JavaScript confirmation dialogs with detailed warnings:

- "Are you sure you want to disable 2FA? This will delete your TOTP secret and all backup codes."
- "Are you sure you want to reset 2FA? This will delete your current TOTP secret and backup codes. You'll need to set up 2FA again with a new QR code."
- "Are you sure you want to clear all trusted devices? All users will need to verify their 2FA code on their next login."

These dialogs prevent accidental destructive operations and inform users about the consequences.

## Cryptographic Security

### TOTP Algorithm

**As of version 6.3.0**: The bundle uses SHA-256 as the TOTP algorithm (upgraded from SHA-1), providing stronger cryptographic security:

```php
// Current configuration (hardcoded in _TotpTrait)
Algorithm: SHA-256
Period: 30 seconds
Digits: 6
```

SHA-1 is considered cryptographically weak and has been replaced with SHA-256 for all new installations.

### Backup Codes

Backup codes are generated using PHP's `random_int()` function, which provides cryptographically secure random numbers:
- 6-digit codes
- Maximum 10 backup codes per user
- Duplicate prevention during generation
- Codes are invalidated after use

## Known Limitations

The following intentional limitations exist. For details, see `CLAUDE.md` in the project root:

1. **Rate Limiting** (#2): Forgot 2FA email sending has no built-in rate limiting. Implement rate limiting at the infrastructure level (firewall, API gateway) or use Symfony's RateLimiter component.

2. **Admin Pagination** (#3): The admin user list uses `findAll()` without pagination. For large user bases, implement custom pagination in your application.

3. **Backup Code Generation** (#7): The `generateBackCodes()` method has a theoretical infinite loop risk with duplicate codes, but the probability is extremely low (~0.0000001%) with 900,000 possible 6-digit codes.

4. **Hardcoded TOTP Configuration** (#10, #15): TOTP parameters (algorithm, period, digits) are currently hardcoded. The defaults follow industry standards and work for 99% of use cases.

## Security Best Practices

When using this bundle, follow these recommendations:

1. **Use HTTPS**: Always use HTTPS in production to protect TOTP secrets and backup codes during transmission
2. **Implement Rate Limiting**: Add rate limiting for the forgot 2FA functionality
3. **Regular Security Audits**: Periodically review user accounts with 2FA enabled
4. **Monitor Logs**: Review PHP error logs for any unusual TotpLogger exceptions
5. **Keep Updated**: Regularly update the bundle and its dependencies for security patches
6. **Backup User Data**: Ensure proper backups before upgrading, especially for breaking changes

## Reporting Security Issues

If you discover a security vulnerability, please email the maintainer directly at `dev@sv-systems.com` rather than using the public issue tracker.
