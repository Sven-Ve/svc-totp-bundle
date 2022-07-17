<?php

declare(strict_types=1);

namespace Svc\TotpBundle\Tests;

use PHPUnit\Framework\TestCase;
use Svc\TotpBundle\Service\TotpDefaultLogger;
use Svc\TotpBundle\Service\TotpLogger;
use Svc\TotpBundle\Service\TotpLoggerInterface;

class LoggerTest extends TestCase
{
  private $container;

  public function setUp(): void
  {
    $kernel = new SvcTotpTestingKernel('test', true);
    $kernel->boot();
    $this->container = $kernel->getContainer();
  }

  public function testLogger()
  {
    $logger = $this->container->get('Svc\TotpBundle\Service\TotpLogger');
    $this->assertInstanceOf(TotpLogger::class, $logger);

    $this->assertTrue($logger->log('test', TotpLoggerInterface::LOG_TOTP_CLEAR_TD, 1));

    $this->expectException(\ArgumentCountError::class);
    $logger->log();
  }

  public function testDefaultLogger()
  {
    $logger = $this->container->get('Svc\TotpBundle\Service\TotpDefaultLogger');
    $this->assertInstanceOf(TotpDefaultLogger::class, $logger);

    $this->assertTrue($logger->log('test', TotpLoggerInterface::LOG_TOTP_CLEAR_TD, 1));
  }
}
