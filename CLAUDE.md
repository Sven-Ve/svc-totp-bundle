# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SvcTotpBundle is a Symfony bundle that provides a user interface for the SchebTwoFactorBundle. It implements TOTP (Time-based One-Time Password), backup codes, and trusted devices functionality for 2FA authentication.

**Key Technologies:**
- Symfony 6.3+ / 7.0+
- PHP 8.2+
- Doctrine ORM 2.11+ / 3.0+
- Scheb 2FA Bundle v7.0
- Endroid QR Code Bundle v6

## Development Commands

### Testing
```bash
# Run PHPUnit tests
composer test
# or directly
vendor/bin/phpunit --testdox

# Run single test class
vendor/bin/phpunit --testdox --filter=ClassNameTest

# Run PHPStan static analysis
composer phpstan
# or directly
vendor/bin/phpstan analyse src/ --level 5 -c .phpstan.neon
```

### Code Quality
```bash
# PHP CS Fixer (code formatting) - uses Symfony + PSR12 rules with header comments
/opt/homebrew/bin/php-cs-fixer fix
# or check without fixing
/opt/homebrew/bin/php-cs-fixer fix --dry-run --diff
```

### Dependency Management
```bash
# Install dependencies
composer install --prefer-dist --no-progress

# Validate composer files
composer validate --strict
```

## Architecture

### Bundle Structure
- **Controllers**: Handle 2FA management, admin functions, and forgot 2FA workflows
  - `TotpController` - Main 2FA management (enable/disable, QR codes, backup codes)
  - `TotpAdminController` - Admin functions for managing user 2FA
  - `TotpForgotController` - "Forgot 2FA" functionality

- **Services**:
  - `TotpLogger` - Logging service with configurable backend
  - `_TotpTrait` - Common functionality shared across controllers

- **Templates**: Twig templates for all 2FA user interfaces located in `templates/`

### Configuration
Bundle configuration in `src/SvcTotpBundle.php` supports:
- `home_path` - Homepage path for redirects (default: 'home')
- `loggingClass` - Custom logging class
- `enableForgot2FA` - Enable/disable forgot 2FA functionality

Services are configured in `config/services.php` and routes in `config/routes.php` using the modern PHP configuration format.

### Dependencies Integration
- Integrates with SchebTwoFactorBundle for core 2FA functionality
- Uses Endroid QR Code Bundle for QR code generation
- Uses Doctrine ORM for data persistence

### Testing
- PHPUnit tests in `tests/` directory
- Dummy entities and test kernel for testing
- PHPStan configuration ignores App\ namespace classes (expected in host applications)

## Important Notes
- Bundle requires Symfony 6.1+ due to new Bundle Configuration System
- User entity and repository are expected to be provided by host application (App\Entity\User, App\Repository\UserRepository)
- Templates can be overridden in host applications following Symfony conventions

### Code Quality Requirements
- **Testing**: All changes must pass `composer test` (PHPUnit with --testdox)
- **Static Analysis**: Code must pass `composer phpstan` (level 5 analysis)
- **Test Coverage**: New features require comprehensive unit and integration tests
- CHANGELOG.md wird durch bin/release.php automatisch aktualisiert, also muessen aenderungen in bin/release.php gemacht werden