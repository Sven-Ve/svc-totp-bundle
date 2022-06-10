<?php

declare(strict_types=1);

namespace Svc\TotpBundle\Service;

use ArgumentCountError;
use Exception;

class TotpLogger
{
  public function __construct(private TotpLoggerInterface $logger, private ?string $env)
  {
  }

  public function log(string $text, int $logType, int $userId): void
  {
    try {
      $this->logger->log($text, $logType, $userId);
    } catch (ArgumentCountError $e) {
      if ($this->env === 'dev') {
        throw $e;
      }
    } catch (Exception $e) {
      if ($this->env === 'dev') {
        throw $e;
      }
    }
  }
}
