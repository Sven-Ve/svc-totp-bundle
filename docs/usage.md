# Usage

## Define your settings 
```yaml
# /config/packages/svc_totp.yaml
# Default configuration for "SvcTotpBundle"
svc_totp:


```


```php
...
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;
...

#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface, TrustedDeviceInterface, BackupCodeInterface
```

```yaml
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

```yaml
# security.yaml
      two_factor:
          auth_form_path: 2fa_login
          check_path: 2fa_login_check
          enable_csrf: true
```