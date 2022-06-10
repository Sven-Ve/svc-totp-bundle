# Logging

## Enable logging

If you want to log your 2FA events, you have to create a class for it and add it to the configuration.<br/>
Example:

```yaml
svc_totp:
    ...

    # Class to call for logging function. See doc for more information
    loggingClass:         App\Service\TotpLogger
```

## Logging class

Your class have to implement Svc\TotpBundle\Service\TotpLoggerInterface and do the logging in a function called log().<br/>
Example:

```php
namespace App\Service;

use Svc\TotpBundle\Service\TotpLoggerInterface;

class TotpLogger implements TotpLoggerInterface
{
  public function log(string $text, int $logType, int $userId): void
  {
    // do somthing with the parameters
  }
```

Paramter:
   * $text: event description
   * $logType: see next chapter
   * $userId: user id, for which this action is executed (not necessarily the executing user, an admin can disable 2FA for other users)

## Log types

The following log types are defined in Svc\TotpBundle\Service\TotpLoggerInterface
```php
  public const LOG_TOTP_SHOW_QR = 1;
  public const LOG_TOTP_ENABLE = 2;
  public const LOG_TOTP_DISABLE = 3;
  public const LOG_TOTP_RESET = 4;
  public const LOG_TOTP_CLEAR_TD = 5;
  public const LOG_TOTP_DISABLE_BY_ADMIN = 6;
  public const LOG_TOTP_RESET_BY_ADMIN = 7;
  public const LOG_TOTP_CLEAR_TD_BY_ADMIN = 8;
```