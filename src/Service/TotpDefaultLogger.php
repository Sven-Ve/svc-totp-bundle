<?php

namespace Svc\TotpBundle\Service;

class TotpDefaultLogger implements TotpLoggerInterface
{
  public function log(string $text, int $logType, int $userId): bool
  {
    // do nothing...
    return true;
  }
}
