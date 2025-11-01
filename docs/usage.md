# Usage

## Breaking Changes

**⚠️ As of version 6.6.0** (2025-01):
- **HTTP Method Enforcement**: All routes now enforce HTTP method restrictions (GET or POST only)
- **Rate Limiting Required**: symfony/rate-limiter dependency required and **must be manually configured**
- **Non-nullable Backup Codes**: `$backupCodes` array type is now non-nullable in `_TotpTrait`
- See [Upgrading from 6.4.x to 6.6.0](#upgrading-from-64x-to-660) for migration guide

**⚠️ As of version 6.4.0**: All state-changing operations now require CSRF protection and use POST requests instead of GET requests. If you have custom forms or integrations, you must update them to include CSRF tokens. See the [Security documentation](security.md) for details.

**⚠️ As of version 6.3.0**: TOTP algorithm upgraded from SHA-1 to SHA-256 for enhanced security. **All existing users must re-setup their 2FA codes** as SHA-256 generates different TOTP codes than SHA-1. This security upgrade is necessary to replace the cryptographically weak SHA-1 algorithm.

**⚠️ As of version 6.2.0**: The `MfaCrudController` for EasyAdminBundle integration has been removed. If you were using the EasyAdmin integration, please use the built-in admin interface (`svc_totp_user_admin` route) or implement your own admin functionality using the existing controller methods.

## Upgrading from 6.4.x to 6.6.0

### 1. Update Composer Dependencies

```bash
composer require symfony/rate-limiter:^7.2
composer update svc/totp-bundle
```

### 2. Configure Rate Limiter

Add rate limiter configuration to `config/packages/framework.yaml` or create `config/packages/rate_limiter.yaml`:

```yaml
framework:
    rate_limiter:
        svc_totp_forgot_2fa:
            policy: 'sliding_window'
            limit: 3
            interval: '15 minutes'
```

**Without this configuration**, the application will fail to start with a detailed error message.

### 3. Update Templates with POST-only Routes

If you have custom templates that use the following routes, you **must** convert them from GET links to POST forms with CSRF tokens:

**Routes requiring POST:**
- `svc_totp_enable` - Enable 2FA
- `svc_totp_disable` - Disable 2FA
- `svc_totp_cleartd` - Clear trusted devices
- `svc_totp_oth_disable` - Admin disable 2FA for other users
- `svc_totp_clear_oth_td` - Admin clear trusted devices for other users

**Example migration** for user list templates:

**Before (6.4.x):**
```twig
{% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
  <a href="{{ path('svc_totp_oth_disable', {'id': user.id}) }}">Disable 2FA</a>
  |
  <a href="{{ path('svc_totp_clear_oth_td', {'id': user.id}) }}">Clear TD</a>
{% endif %}
```

**After (6.6.0):**
```twig
{% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
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
```

**Routes that remain GET-only** (no changes needed):
- `svc_totp_manage` - Manage 2FA page (view)
- `svc_totp_qrcode` - QR code image
- `svc_totp_user_admin` - Admin interface (view)
- `svc_totp_verify_forgot` - Forgot 2FA email verification

See [Security documentation](security.md#integration-in-user-lists-admin-area) for complete examples.

### 4. Testing the Upgrade

After upgrading:

1. Clear Symfony cache: `php bin/console cache:clear`
2. Test that the application starts without errors (rate limiter validation)
3. Test admin operations in user lists work correctly with POST forms
4. Test the "Forgot 2FA" functionality respects rate limiting

## Important Security Notes

- **All operations now require POST requests with CSRF tokens** for security
- **Destructive actions include confirmation dialogs** to prevent accidental data loss
- **Rate limiting prevents abuse** of email-based 2FA reset functionality
- See the [Security documentation](security.md) for complete security features and best practices

## Controller

### Enable/Disable 2FA

call the path "app_totp_manage" in your twig template or controller<br/>
Example:

```html
  <a class="dropdown-item" href="{{ path('svc_totp_manage') }}">
    <i class="fa-solid fa-shield"></i>
    {% trans %}Manage 2FA{% endtrans %}
  </a>
```

You can add the parameter reset=true, then 2FA will be disabled and the shared secret will be deleted (you will need a new secret/QR code and you will have to store it in the authenticator again).<br/>
Example:
```html
  <a class="dropdown-item" href="{{ path('svc_totp_manage', {'reset': true}) }}">
    ...
  </a>
```

### Disable 2FA for other users (if you do not want to use the admin interface)

**⚠️ Important**: As of version 6.6.0, this operation requires a POST request with CSRF token.

Use the "svc_totp_oth_disable" path in your Twig template and set the parameter id to the user id.<br/>
Example:

```twig
{% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
  <form method="post" action="{{ path('svc_totp_oth_disable', {'id': user.id}) }}"
        style="display: inline;"
        onsubmit="return confirm('Are you sure you want to disable 2FA for user {{ user.email }}?');">
    <input type="hidden" name="_csrf_token" value="{{ csrf_token('totp-admin-disable') }}">
    <button type="submit" class="btn btn-sm btn-warning">
      {% trans %}Disable 2FA{% endtrans %}
    </button>
  </form>
{% endif %}
```

You can add the parameter reset=true, then 2FA will be disabled and the shared secret will be deleted (you will need a new secret/QR code and you will have to store it in the authenticator again).<br/>
Example:
```twig
<form method="post" action="{{ path('svc_totp_oth_disable', {'id': user.id, 'reset': true}) }}"
      style="display: inline;"
      onsubmit="return confirm('Are you sure you want to reset 2FA for user {{ user.email }}? This will delete their TOTP secret and all backup codes.');">
  <input type="hidden" name="_csrf_token" value="{{ csrf_token('totp-admin-disable') }}">
  <button type="submit" class="btn btn-sm btn-danger">
    {% trans %}Reset 2FA{% endtrans %}
  </button>
</form>
```

### Clear trusted devices for current/all users (if you do not want to use the admin interface)

**⚠️ Important**: As of version 6.6.0, this operation requires a POST request with CSRF token.

Use the "svc_totp_cleartd" path in your Twig template.<br/>
Set the "allUsers" parameter to true if you want to delete trusted devices for all users (requires ROLE_ADMIN), otherwise (default) only devices for the current user will be deleted.<br/>
Example:

```twig
<form method="post" action="{{ path('svc_totp_cleartd', {'allUsers': true}) }}"
      style="display: inline;"
      onsubmit="return confirm('Are you sure you want to clear all trusted devices for all users?');">
  <input type="hidden" name="_csrf_token" value="{{ csrf_token('totp-admin-clear-trusted') }}">
  <button type="submit" class="dropdown-item">
    <i class="fa-solid fa-folder-minus"></i>
    {% trans %}Clear trusted devices{% endtrans %} ({% trans %}all{% endtrans %})
  </button>
</form>
```

### Clear trusted devices for other users (if you do not want to use the admin interface)

**⚠️ Important**: As of version 6.6.0, this operation requires a POST request with CSRF token.

Use the "svc_totp_clear_oth_td" path in your Twig template and set the parameter id to the user id.<br/>
Example:

```twig
{% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
  <form method="post" action="{{ path('svc_totp_clear_oth_td', {'id': user.id}) }}"
        style="display: inline;"
        onsubmit="return confirm('Are you sure you want to clear trusted devices for user {{ user.email }}?');">
    <input type="hidden" name="_csrf_token" value="{{ csrf_token('totp-admin-clear-trusted') }}">
    <button type="submit" class="btn btn-sm btn-primary">
      {% trans %}Clear TD{% endtrans %}
    </button>
  </form>
{% endif %}
```

### Call an admin interface

list all user with the possibility to disable/reset 2FA, clear trusted devices and show infos about 2FA by user

Example:<br/>
```html
{% if is_granted("ROLE_ADMIN") %}
  <a href="{{ path('svc_totp_user_admin', {'id' : user.id}) }}">{% trans %}2FA User admin{% endtrans %}<a/>
{% endif %}
```

