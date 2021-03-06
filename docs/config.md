# Configuration

## Routes

add a new file config/routes/svc_totp.yaml

```yaml
#config/routes/svc_totp.yaml
_svc_totp:
    resource: '@SvcTotpBundle/config/routes.yaml'
    prefix: /mfa/{_locale}
```

adapt the routing information in config/routes/scheb_2fa.yaml (only if you like language support) - add {_locale}

```yaml
#config/routes/scheb_2fa.yaml
2fa_login:
    path: /2fa/{_locale}
    defaults:
        _controller: "scheb_two_factor.form_controller::form"

2fa_login_check:
    path: /2fa_check
```

## Bundle configuration
```yaml
#config/packages/svc_totp.yaml
svc_totp:

    # Default Homepage path for redirecting after actions
    home_path:            home

    # Class to call for logging function. See documentation (capture logging) for more information
    loggingClass:         ~
```

## Security configuration

Enable TOTP (2FA) in config/packages/security.yaml under your main firewall(s)
```yaml
# config/packages/security.yaml
main:
    ...
    two_factor:
        auth_form_path: 2fa_login
        check_path: 2fa_login_check
        enable_csrf: true
```

add the following routes to your access_control list before all other routes: 
```yaml
# config/packages/security.yaml
# Easy way to control access for large sections of your site
# Note: Only the *first* access control that matches will be used
access_control:
    - { path: ^/logout, role: PUBLIC_ACCESS }
    - { path: ^/2fa, role: IS_AUTHENTICATED_2FA_IN_PROGRESS }    
```

## TOTP configuration

Enable TOTP, trusted devices and backup_codes in config/packages/scheb_2fa.yaml
```yaml
# config/packages/scheb_2fa.yaml
# See the configuration reference at https://symfony.com/bundles/SchebTwoFactorBundle/6.x/configuration.html
scheb_two_factor:
    security_tokens:
        - Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
        - Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken

    totp:
        enabled: true
        issuer: 'shorter.li'
        template: "@SvcTotp/totp/enterTotp.html.twig"

    trusted_device:
        enabled: true
        extend_lifetime: true

    backup_codes:
        enabled: true
```       

## Configure the User entity

Implement the interfaces
* Scheb\TwoFactorBundle\Model\BackupCodeInterface;
* Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
* Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;

Load Svc\TotpBundle\Service\_TotpTrait (TOTP field definitions and gether/setter functions)


```php
...
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;
use Svc\TotpBundle\Service\_TotpTrait;
...
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface, TrustedDeviceInterface, BackupCodeInterface
{
  use _TotpTrait;
  ...
```

Create a migration to update the user table

## Enable Forget QR Code function

This function is disabled by default. Enable it in svc_totp.yaml:
```yaml
#config/packages/svc_totp.yaml
svc_totp:
  ...
  # Is "Forgot 2FA" functionality enabled?
  enableForgot2FA:      true
```

and add the path to the reset method to security.yaml right after the two other 2FA paths
```yaml
# config/packages/security.yaml
access_control:
    - ...
    - { path: ^/mfa/.*/forgot, role: IS_AUTHENTICATED_2FA_IN_PROGRESS }
```
