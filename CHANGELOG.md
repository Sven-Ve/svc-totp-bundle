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
