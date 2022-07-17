# Svc/SvcTotpBundle

[![CI](https://github.com/Sven-Ve/svc-totp-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/Sven-Ve/svc-totp-bundle/actions/workflows/php.yml) 
[![Latest Stable Version](https://poser.pugx.org/svc/totp-bundle/v)](https://packagist.org/packages/svc/totp-bundle) 
[![License](https://poser.pugx.org/svc/totp-bundle/license)](https://packagist.org/packages/svc/totp-bundle) 
[![Total Downloads](https://poser.pugx.org/svc/totp-bundle/downloads)](https://packagist.org/packages/svc/totp-bundle)
[![PHP Version Require](http://poser.pugx.org/svc/totp-bundle/require/php)](https://packagist.org/packages/svc/totp-bundle)

:warning: **Attention:** <br/>
From version 4.0 the bundle works only with Symfony >=6.1, because the new Bundle Configuration System is used.<br/>
Please use version 1.x for older Symfony installations.<br/>
*The version jump comes from the synchronization of all svc bundle versions.*

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
* [Logging](docs/logging.md)

## Screenshots

### Enable 2FA

![Enable 2FA](/docs/images/2fa-enabled.png)

### Disable 2FA

![Disable 2FA](/docs/images/2fa-disabled.png)

### Backup codes

![Backup codes](/docs/images/backup-codes.png)

### Enter 2FA code

![Enter 2FA code](/docs/images/2fa-enter-code.png)
