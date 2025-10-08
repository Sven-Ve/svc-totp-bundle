<?php

declare(strict_types=1);

/*
 * This file is part of the SvcTotp bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @doesNotPerformAssertions is not used because we do have assertions.
     * The risky warning comes from error_log() in TotpLogger, which is expected behavior.
     */
    public function testLogger(): void
    {
        $logger = $this->container->get('Svc\TotpBundle\Service\TotpLogger');
        $this->assertInstanceOf(TotpLogger::class, $logger);

        $result = $logger->log('test', TotpLoggerInterface::LOG_TOTP_CLEAR_TD, 1);
        $this->assertTrue($result);
    }

    /**
     * The risky warning comes from error_log() in TotpLogger, which is expected behavior.
     */
    public function testLoggerMissingArguments(): void
    {
        $logger = $this->container->get('Svc\TotpBundle\Service\TotpLogger');

        $this->expectException(\ArgumentCountError::class);
        $logger->log();
    }

    /**
     * The risky warning comes from error_log() in TotpLogger, which is expected behavior.
     */
    public function testDefaultLogger(): void
    {
        $logger = $this->container->get('Svc\TotpBundle\Service\TotpDefaultLogger');
        $this->assertInstanceOf(TotpDefaultLogger::class, $logger);

        $result = $logger->log('test', TotpLoggerInterface::LOG_TOTP_CLEAR_TD, 1);
        $this->assertTrue($result);
    }

    /**
     * The risky warning comes from error_log() in TotpLogger, which is expected behavior.
     */
    public function testLoggerWithDifferentLogTypes(): void
    {
        $logger = $this->container->get('Svc\TotpBundle\Service\TotpLogger');

        $this->assertTrue($logger->log('Show QR code', TotpLoggerInterface::LOG_TOTP_SHOW_QR, 1));
        $this->assertTrue($logger->log('Enable TOTP', TotpLoggerInterface::LOG_TOTP_ENABLE, 2));
        $this->assertTrue($logger->log('Disable TOTP', TotpLoggerInterface::LOG_TOTP_DISABLE, 3));
        $this->assertTrue($logger->log('Reset TOTP', TotpLoggerInterface::LOG_TOTP_RESET, 4));
        $this->assertTrue($logger->log('Clear trusted devices', TotpLoggerInterface::LOG_TOTP_CLEAR_TD, 5));
        $this->assertTrue($logger->log('Disable TOTP by admin', TotpLoggerInterface::LOG_TOTP_DISABLE_BY_ADMIN, 6));
        $this->assertTrue($logger->log('Reset TOTP by admin', TotpLoggerInterface::LOG_TOTP_RESET_BY_ADMIN, 7));
        $this->assertTrue($logger->log('Clear trusted devices by admin', TotpLoggerInterface::LOG_TOTP_CLEAR_TD_BY_ADMIN, 8));
    }

    /**
     * The risky warning comes from error_log() in TotpLogger, which is expected behavior.
     */
    public function testDefaultLoggerWithDifferentLogTypes(): void
    {
        $logger = $this->container->get('Svc\TotpBundle\Service\TotpDefaultLogger');

        $this->assertTrue($logger->log('Show QR code', TotpLoggerInterface::LOG_TOTP_SHOW_QR, 1));
        $this->assertTrue($logger->log('Enable TOTP', TotpLoggerInterface::LOG_TOTP_ENABLE, 2));
        $this->assertTrue($logger->log('Disable TOTP', TotpLoggerInterface::LOG_TOTP_DISABLE, 3));
        $this->assertTrue($logger->log('Reset TOTP', TotpLoggerInterface::LOG_TOTP_RESET, 4));
        $this->assertTrue($logger->log('Clear trusted devices', TotpLoggerInterface::LOG_TOTP_CLEAR_TD, 5));
        $this->assertTrue($logger->log('Disable TOTP by admin', TotpLoggerInterface::LOG_TOTP_DISABLE_BY_ADMIN, 6));
        $this->assertTrue($logger->log('Reset TOTP by admin', TotpLoggerInterface::LOG_TOTP_RESET_BY_ADMIN, 7));
        $this->assertTrue($logger->log('Clear trusted devices by admin', TotpLoggerInterface::LOG_TOTP_CLEAR_TD_BY_ADMIN, 8));
    }
}
