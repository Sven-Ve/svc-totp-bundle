<?php

namespace Svc\TotpBundle\Tests;

use PHPUnit\Framework\TestCase;
use Svc\TotpBundle\Service\TotpDefaultLogger;
use Svc\TotpBundle\Service\TotpLoggerInterface;

class FunctionalTest extends TestCase
{

  public function testServiceWiring()
  {
    $kernel = new SvcTotpTestingKernel('test', true);
    $kernel->boot();
    $container = $kernel->getContainer();

    $logger = $container->get('svc_totp.service.default_logger');
    $this->assertInstanceOf(TotpDefaultLogger::class, $logger);

    $this->assertNull($logger->log('test', TotpLoggerInterface::LOG_TOTP_CLEAR_TD, 1));
  }

}
