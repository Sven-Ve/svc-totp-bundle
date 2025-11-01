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
- `totp-admin-disable` - Admin disable 2FA for other users
- `totp-admin-reset` - Admin reset 2FA for other users
- `totp-admin-clear-trusted` - Admin clear trusted devices for other users
- `totp-forgot` - Forgot 2FA verification

### Integration in User Lists (Admin Area)

**As of version 6.6.0**, all admin operations require POST requests with CSRF tokens. If you're building a user list with 2FA management capabilities (e.g., in an admin panel), you need to use POST forms instead of GET links.

**Complete Example**: User list with inline 2FA management forms:

```twig
<table class="table">
  <thead>
    <tr>
      <th>Email</th>
      <th>Roles</th>
      <th>2FA Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  {% for user in users %}
    <tr>
      <td>{{ user.email }}</td>
      <td>{{ user.roles|json_encode }}</td>
      <td>{{ user.isTotpAuthenticationEnabled ? 'Enabled' : 'Disabled' }}</td>
      <td>
        <a href="{{ path('app_user_show', {'id': user.id}) }}">show</a>
        |
        <a href="{{ path('app_user_edit', {'id': user.id}) }}">edit</a>

        {% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
          |
          {# Disable 2FA for user #}
          <form method="post" action="{{ path('svc_totp_oth_disable', {'id': user.id}) }}"
                style="display: inline;"
                onsubmit="return confirm('Are you sure you want to disable 2FA for user {{ user.email }}?');">
            <input type="hidden" name="_csrf_token" value="{{ csrf_token('totp-admin-disable') }}">
            <button type="submit" class="btn btn-sm btn-warning"
                    style="padding: 0; border: none; background: none; color: #007bff; text-decoration: underline; cursor: pointer;">
              Disable 2FA
            </button>
          </form>
          |
          {# Clear trusted devices for user #}
          <form method="post" action="{{ path('svc_totp_clear_oth_td', {'id': user.id}) }}"
                style="display: inline;"
                onsubmit="return confirm('Are you sure you want to clear trusted devices for user {{ user.email }}?');">
            <input type="hidden" name="_csrf_token" value="{{ csrf_token('totp-admin-clear-trusted') }}">
            <button type="submit" class="btn btn-sm btn-primary"
                    style="padding: 0; border: none; background: none; color: #007bff; text-decoration: underline; cursor: pointer;">
              Clear TD
            </button>
          </form>
        {% endif %}
      </td>
    </tr>
  {% endfor %}
  </tbody>
</table>
```

**Key Points for User List Integration:**

1. **Inline Forms**: Use `display: inline;` to keep the forms in line with other links
2. **Button Styling**: Style buttons to look like links with `text-decoration: underline; cursor: pointer;`
3. **CSRF Tokens**: Always include the correct CSRF token for each operation:
   - `totp-admin-disable` for disabling 2FA for other users
   - `totp-admin-clear-trusted` for clearing trusted devices for other users
4. **Confirmation Dialogs**: Use `onsubmit` with `confirm()` to prevent accidental actions
5. **Conditional Display**: Only show 2FA management options when user has 2FA enabled and current user has admin rights

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

## Rate Limiting

**As of version 6.6.0**: Built-in rate limiting is now implemented for the "Forgot 2FA" functionality to prevent abuse.

### Configuration

**Important**: Rate limiting requires manual configuration. You must add the rate limiter configuration to your application.

Add the following to your `config/packages/framework.yaml` or create a new file `config/packages/rate_limiter.yaml`:

```yaml
framework:
    rate_limiter:
        svc_totp_forgot_2fa:
            policy: 'sliding_window'
            limit: 3
            interval: '15 minutes'
```

**Without this configuration**, the application will fail to start during container compilation. The bundle includes a compiler pass that validates the rate limiter configuration and displays a helpful error message with the exact configuration needed:

```
╔════════════════════════════════════════════════════════════════════════════════╗
║                                                                                ║
║  SvcTotpBundle Configuration Error: Rate Limiter Not Configured               ║
║                                                                                ║
╚════════════════════════════════════════════════════════════════════════════════╝

The "Forgot 2FA" functionality requires rate limiting to be configured, but the
rate limiter service "limiter.svc_totp_forgot_2fa" was not found.

SOLUTION:
─────────
Add the following configuration to your application:
[... detailed configuration example ...]
```

This compile-time validation ensures you cannot deploy an application with missing rate limiter configuration.

The recommended configuration limits forgot 2FA requests to **3 requests per 15 minutes per IP address**.

### Customization

You can customize the rate limiting settings by overriding this configuration in your application:

```yaml
# config/packages/svc_totp.yaml or config/packages/rate_limiter.yaml
framework:
    rate_limiter:
        svc_totp_forgot_2fa:
            policy: 'sliding_window'  # or 'fixed_window', 'token_bucket'
            limit: 5                   # Number of allowed requests
            interval: '10 minutes'     # Time window
```

### User Experience

When a user exceeds the rate limit:
- They receive a flash message: "Too many requests. Please try again later."
- They are redirected to the home page
- The failed attempt is still counted against their limit

### Technical Details

- **Limiter Key**: Uses the client's IP address (`$request->getClientIp()`)
- **Policy**: Sliding window (more accurate than fixed window)
- **Scope**: Only applies to the email sending step of forgot 2FA
- **Storage**: Uses Symfony's default cache adapter (can be customized)

For production environments with load balancers or proxies, ensure your Symfony application is properly configured to read the real client IP from forwarded headers.

## Known Limitations

The following intentional limitations exist. For details, see `CLAUDE.md` in the project root:

1. **Admin Pagination** (#3): The admin user list uses `findAll()` without pagination. For large user bases, implement custom pagination in your application.

2. **Backup Code Generation** (#7): The `generateBackCodes()` method has a theoretical infinite loop risk with duplicate codes, but the probability is extremely low (~0.0000001%) with 900,000 possible 6-digit codes.

3. **Hardcoded TOTP Configuration** (#10, #15): TOTP parameters (algorithm, period, digits) are currently hardcoded. The defaults follow industry standards and work for 99% of use cases.

## Security Best Practices

When using this bundle, follow these recommendations:

1. **Use HTTPS**: Always use HTTPS in production to protect TOTP secrets and backup codes during transmission
2. **Configure Trusted Proxies**: If behind a load balancer or proxy, configure Symfony's trusted proxies to ensure rate limiting works correctly with real client IPs
3. **Regular Security Audits**: Periodically review user accounts with 2FA enabled
4. **Monitor Logs**: Review PHP error logs for any unusual TotpLogger exceptions or rate limiting patterns
5. **Keep Updated**: Regularly update the bundle and its dependencies for security patches
6. **Backup User Data**: Ensure proper backups before upgrading, especially for breaking changes
7. **Review Rate Limits**: Adjust rate limiting thresholds based on your application's usage patterns and security requirements

## Reporting Security Issues

If you discover a security vulnerability, please email the maintainer directly at `dev@sv-systems.com` rather than using the public issue tracker.
