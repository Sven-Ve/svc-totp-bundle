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

# Run all quality checks together (useful before committing)
composer phpstan && composer test && /opt/homebrew/bin/php-cs-fixer fix --dry-run --diff
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
- `fromEmail` - Email address to use as sender for 2FA reset emails (default: null)

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

### Security & Code Quality Improvements (2025-01)

The following improvements were implemented based on security audit and code quality review:

**Security Enhancements:**
- **CSRF Protection** (#1): All state-changing operations (enable/disable 2FA, clear trusted devices, forgot 2FA) now require valid CSRF tokens using Symfony's `#[IsCsrfTokenValid()]` attribute. All actions converted from GET to POST requests.
- **Input Validation** (#11): User ID validation in forgot 2FA verification now checks for numeric positive integers to prevent type errors and invalid requests.

**Type Safety:**
- **Null Safety** (#4): Added `instanceof User` checks for all `getUser()` calls (11 locations) to prevent potential null pointer exceptions, with proper access denied exceptions.
- **Code Cleanup** (#5): Removed redundant ternary operators (`? true : false`) in favor of explicit null checks (`!== null`), changed `and` to `&&` for consistency.

**Error Handling & Debugging:**
- **Exception Logging** (#9): Logger now logs exceptions to PHP error log even in production before swallowing them, preventing silent failures and enabling production debugging.
- **Better Error Messages** (#12): Improved user-facing error messages with clear explanations (e.g., "Cannot enable 2FA. Please scan the QR code first." instead of "Cannot enable 2FA").

**User Experience:**
- **Confirmation Dialogs** (#13): All destructive actions (disable/reset 2FA, clear trusted devices) now have JavaScript confirmation dialogs with detailed warnings about what will be deleted.

### Known Limitations (Intentionally Not Fixed)
- **Backup Code Generation Loop** (#7): The `generateBackCodes()` method in `TotpController` theoretically has an infinite loop risk if `random_int()` continuously generates duplicate 6-digit codes. However, the probability is extremely low (~0.0000001%) with 900,000 possible codes and only 10 required. The risk/reward ratio does not justify adding complexity for this edge case. This would only become an issue if someone reduces the code length to 3-4 digits or dramatically increases the number of backup codes required.
- **TOTP Configuration** (#10, #15): TOTP algorithm (SHA256), period (30s), digits (6), and max backup codes (10) are currently hardcoded. Making these configurable would require significant architectural changes (dependency injection into traits or interface-based configuration). The current defaults follow industry standards and work for 99% of use cases.
- **Rate Limiting** (#2): Forgot 2FA email sending has no rate limiting. This is left to the host application's infrastructure (e.g., firewall, API gateway) or can be implemented via Symfony's RateLimiter component.
- **Admin Pagination** (#3): Admin user list uses `findAll()` without pagination. For applications with large user bases, implement custom repository methods or use a dedicated admin bundle.
- **Database Indexes** (#8): Performance optimization via database indexes on `isTotpAuthenticationEnabled` should be added via migrations in the host application based on specific performance requirements.

### Code Quality Requirements
- **Testing**: All changes must pass `composer test` (PHPUnit with --testdox)
- **Static Analysis**: Code must pass `composer phpstan` (level 5 analysis)
- **Code Formatting**: Code must pass `/opt/homebrew/bin/php-cs-fixer fix --dry-run --diff`
- **Test Coverage**: New features require comprehensive unit and integration tests
- **Release Process**: CHANGELOG.md is automatically updated via `bin/release.php` - edit that file for changelog entries

### Release Management
```bash
# Release process (automated via bin/release.php)
# 1. Updates version and message in bin/release.php
# 2. Runs phpstan and tests automatically
# 3. Updates CHANGELOG.md with version and message
# 4. Creates git commit and tag
# 5. Pushes to origin
php bin/release.php
```