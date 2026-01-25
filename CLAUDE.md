# CLAUDE.md

Instructions for Claude Code when working with this repository.

## Meta Rules for This File

- Keep CLAUDE.md concise - no detailed examples unless absolutely necessary
- CLAUDE.md is for Claude's instructions only, not app documentation
- App documentation belongs in `docs/` directory
- All documentation must be in English
- Record decisions here to prevent re-suggesting them

## Project Overview

SvcTotpBundle is a Symfony bundle providing UI for SchebTwoFactorBundle (TOTP, backup codes, trusted devices).

**Stack:** Symfony 7.2+, PHP 8.4+, Doctrine ORM 2.11+/3.0+, Scheb 2FA Bundle v7.10+

## Development Commands

```bash
# Testing
composer test                    # PHPUnit with --testdox
composer phpstan                 # Static analysis level 7

# Code formatting
/opt/homebrew/bin/php-cs-fixer fix

# All checks before commit
composer phpstan && composer test && /opt/homebrew/bin/php-cs-fixer fix --dry-run --diff
```

## Code Quality Requirements

- All changes must pass `composer test` and `composer phpstan`
- Code must pass php-cs-fixer (Symfony + PSR12 rules)
- New features require tests with `declare(strict_types=1);`
- PHPStan ignores `App\` namespace (provided by host app)
- Array type hints: use PHPDoc format `@return array<type>`

## Release Process

- Commits and releases via `bin/release.php` only
- CHANGELOG.md updated automatically by release script
- Edit commit message in `bin/release.php`, not CHANGELOG.md directly

## Architecture Decisions (Do Not Re-Suggest)

### CSRF Validation
Use manual `isCsrfTokenValid()` instead of `#[IsCsrfTokenValid]` attribute due to Symfony bug #57343 (attribute causes redirect to login instead of error handling).

### Request Parameters (Symfony 7.4+)
`$request->get()` is deprecated. Use:
- `$request->attributes->get()` for route params
- `$request->query->get()` for GET params
- `$request->request->get()` for POST params
- `#[MapQueryParameter]` attribute for typed GET params

### Type Safety
- All `in_array()` and `array_search()` use strict comparison (`true` as 3rd param)
- All `getUser()` calls have `instanceof User` checks
- `$backupCodes` is typed as `array`, not `?array`

### Performance
- Clearing trusted devices uses single DQL UPDATE query (not loading all users)
- `invalidateBackupCode()` reindexes array after removal

## Known Limitations (Intentionally Not Fixed)

These were evaluated and deliberately not changed:

1. **Backup Code Generation Loop** (#7): Theoretical infinite loop if `random_int()` generates duplicates. Probability ~0.0000001% with 900k possible codes. Not worth added complexity.

2. **Hardcoded TOTP Config** (#10, #15): Algorithm (SHA256), period (30s), digits (6), max backup codes (10) are hardcoded. Making configurable requires major refactoring. Defaults follow industry standards.

3. **Admin Pagination** (#3): `findAll()` without pagination. Host app should implement if needed.

4. **Database Indexes** (#8): Index on `isTotpAuthenticationEnabled` should be added via host app migrations if needed.

## Implemented Security Features

Already implemented (don't suggest again):
- CSRF protection on all state-changing operations
- Rate limiting on forgot 2FA (3 req/15 min, requires host app config)
- HTTP method restrictions (POST for state changes, GET for reads)
- Input validation on user IDs (numeric positive integers)
- Null safety on all `getUser()` calls
- JavaScript confirmation dialogs on destructive actions
- Exception logging before swallowing
- Descriptive alt text on QR codes
