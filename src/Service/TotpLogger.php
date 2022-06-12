<?php

declare(strict_types=1);

namespace Svc\TotpBundle\Service;

use ArgumentCountError;
use Exception;

class TotpLogger
{
  public function __construct(private readonly TotpLoggerInterface $logger, private readonly ?string $env)
  {
  }

  public function log(string $text, int $logType, int $userId): bool
  {
    try {
      $this->logger->log($text, $logType, $userId);

      return true;
    } catch (ArgumentCountError|Exception $e) {
      if ($this->env === 'dev') {
        throw $e;
      }
    }

    return false;
  }
}
