# Svc/SvcTotpBundle

[![CI](https://github.com/Sven-Ve/svc-totp-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/Sven-Ve/svc-totp-bundle/actions/workflows/php.yml) 
[![Latest Stable Version](https://poser.pugx.org/svc/totp-bundle/v)](https://packagist.org/packages/svc/totp-bundle) 
[![License](https://poser.pugx.org/svc/totp-bundle/license)](https://packagist.org/packages/svc/totp-bundle) 
[![Total Downloads](https://poser.pugx.org/svc/totp-bundle/downloads)](https://packagist.org/packages/svc/totp-bundle)
[![PHP Version Require](http://poser.pugx.org/svc/totp-bundle/require/php)](https://packagist.org/packages/svc/totp-bundle)

:warning: **Version Compatibility:** <br/>
- **Version 6.x** (current): Requires Symfony 7.2+, PHP 8.4+
- **Version 5.x**: Compatible with Symfony 6.4 and 7.x, PHP 8.2+
- **Version 4.x**: Compatible with Symfony 6.1+
- **Version 1.x**: For older Symfony installations

**Breaking Changes:**
- *Version 6.4.0+*: All state-changing operations now require CSRF protection and POST methods. See [Usage documentation](docs/usage.md) for migration details.
- *Version 6.6.0* (2025-01): HTTP method restrictions enforced on all routes. Rate limiting requires symfony/rate-limiter dependency **and manual configuration**. Non-nullable `$backupCodes` array type in trait. See [Configuration documentation](docs/config.md#rate-limiting-configuration-version-660) for details.

# Userinterface for the excellent SchebTwoFactorBundle

SchebTwoFactorBundle provides the functions to implement simple 2FA. However, you have to create the user interface yourself.

This small bundle provides a ready to use implementation.

The following functions are enabled:
* TOTP 
* Backup codes
* Trusted devices

## Installation and configuration
* [Installation](docs/installation.md)
* [Configuration](docs/config.md)
* [Usage](docs/usage.md)
* [Security Features](docs/security.md)
* [Logging](docs/logging.md)
* [Styling and Customization](docs/styling.md)

## Screenshots

### Enable 2FA

![Enable 2FA](/docs/images/2fa-enabled.png)

### Disable 2FA

![Disable 2FA](/docs/images/2fa-disabled.png)

### Backup codes

![Backup codes](/docs/images/backup-codes.png)

### Enter 2FA code

![Enter 2FA code](/docs/images/2fa-enter-code.png)
