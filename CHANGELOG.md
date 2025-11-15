# Changelog


## Version 1.0.0
*Mon, 06 Jun 2022 14:30:11 +0000*
- first version added to sv-video


## Version 1.1.0
*Wed, 08 Jun 2022 08:58:23 +0000*
- added controller to disable 2FA/clear trusted devices for other users


## Version 1.2.0
*Fri, 10 Jun 2022 20:29:59 +0000*
- added logging


## Version 1.2.1
*Sun, 12 Jun 2022 09:10:43 +0000*
- improved testing


## Version 1.3.0
*Mon, 20 Jun 2022 20:09:13 +0000*
- forget QR code added


## Version 1.3.1
*Thu, 23 Jun 2022 21:13:06 +0000*
- add missing translation


## Version 4.0.0
*Sun, 17 Jul 2022 13:57:00 +0000*
- build with Symfony 6.1 bundle features, runs only with symfony 6.1


## Version 4.0.1
*Thu, 21 Jul 2022 18:41:57 +0000*
- licence year update


## Version 4.1.0
*Wed, 30 Nov 2022 20:54:06 +0000*
- update for symfony 6.2


## Version 5.0.0
*Sat, 16 Dec 2023 14:56:21 +0000*
- ready for symfony 6.4 and 7


## Version 5.0.1
*Sun, 17 Dec 2023 18:21:47 +0000*
- ready for symfony 6.4 and 7
- fixed tests


## Version 5.1.0
*Fri, 08 Mar 2024 20:45:06 +0000*
- runs with doctrin/orm ^3 too


## Version 6.0.0
*Sun, 27 Oct 2024 15:17:47 +0000*
- runs (only) with endroid/qr-code-bundle >= 6


## Version 6.1.0
*Wed, 24 Sep 2025 20:06:48 +0000*
- breaking change, now it use php as route configuration. 
- You have to import the routes in your project manually. See docs for more information.


## Version 6.2.0
*Thu, 25 Sep 2025 19:09:14 +0000*
- BREAKING: Removed MfaCrudController and EasyAdminBundle integration. Use built-in admin interface (svc_totp_user_admin route) instead.


## Version 6.3.0
*Thu, 25 Sep 2025 19:25:03 +0000*
- BREAKING: Upgraded TOTP algorithm from SHA-1 to SHA-256 for enhanced security. Users must re-setup their 2FA codes. 
- Fixed route configuration bug.


## Version 6.4.0
*Wed, 08 Oct 2025 12:49:36 +0000*
- Improve security, update tests and docs.


## Version 6.5.0
*Mon, 27 Oct 2025 18:20:53 +0000*
- Add strict types declaration across multiple files; update composer.json to support newer doctrine versions


## Version 6.6.0
*Sat, 01 Nov 2025 21:49:20 +0000*
- Breaking Changes: add Rate limiting, CSRF protection, DQL for trusted devices clearing. see documentation for migration steps.


## Version 6.6.1
*Mon, 03 Nov 2025 13:33:56 +0000*
- Brearefactor: remove background color from login form in 2FA templates for consistency and dark mode support


## Version 6.7.0
*Wed, 05 Nov 2025 15:18:11 +0000*
- feat: Add unique CSS classes (svc-totp-*) for custom styling and simplify template structure. Remove generic login-form class and unnecessary wrapper divs. Add comprehensive styling documentation.


## Version 6.8.0
*Sat, 15 Nov 2025 09:54:48 +0000*
- feat: Symfony 7.4 compatibility - Replace deprecated $request->get() with #[MapQueryParameter] attribute and add comprehensive request handling documentation to CLAUDE.md
