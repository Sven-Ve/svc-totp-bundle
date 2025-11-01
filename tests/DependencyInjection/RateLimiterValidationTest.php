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

namespace Svc\TotpBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Svc\TotpBundle\DependencyInjection\Compiler\RateLimiterValidationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Tests for RateLimiterValidationPass.
 */
class RateLimiterValidationTest extends TestCase
{
    /**
     * Test that validation passes when rate limiter service exists.
     */
    public function testValidationPassesWithRateLimiterConfigured(): void
    {
        $container = new ContainerBuilder();

        // Add the TotpForgotController definition
        $container->setDefinition(
            'Svc\TotpBundle\Controller\TotpForgotController',
            new Definition()
        );

        // Add the rate limiter service
        $container->set('limiter.svc_totp_forgot_2fa', new \stdClass());

        $pass = new RateLimiterValidationPass();

        // Should not throw an exception
        $pass->process($container);

        $this->assertTrue(true, 'Validation should pass when rate limiter is configured');
    }

    /**
     * Test that validation throws exception when rate limiter is missing.
     */
    public function testValidationFailsWithoutRateLimiterConfigured(): void
    {
        $container = new ContainerBuilder();

        // Add the TotpForgotController definition but NO rate limiter service
        $container->setDefinition(
            'Svc\TotpBundle\Controller\TotpForgotController',
            new Definition()
        );

        $pass = new RateLimiterValidationPass();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Rate Limiter Not Configured/');
        $this->expectExceptionMessageMatches('/limiter\.svc_totp_forgot_2fa/');
        $this->expectExceptionMessageMatches('/framework:\s+rate_limiter:/');

        $pass->process($container);
    }

    /**
     * Test that validation is skipped when TotpForgotController doesn't exist.
     */
    public function testValidationSkippedWithoutController(): void
    {
        $container = new ContainerBuilder();

        // Don't add the TotpForgotController definition
        // and don't add the rate limiter service

        $pass = new RateLimiterValidationPass();

        // Should not throw an exception because controller doesn't exist
        $pass->process($container);

        $this->assertTrue(true, 'Validation should be skipped when controller does not exist');
    }

    /**
     * Test that error message contains helpful configuration example.
     */
    public function testErrorMessageContainsConfigurationExample(): void
    {
        $container = new ContainerBuilder();

        $container->setDefinition(
            'Svc\TotpBundle\Controller\TotpForgotController',
            new Definition()
        );

        $pass = new RateLimiterValidationPass();

        try {
            $pass->process($container);
            $this->fail('Expected RuntimeException to be thrown');
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();

            // Verify the error message contains helpful information
            $this->assertStringContainsString('framework:', $message);
            $this->assertStringContainsString('rate_limiter:', $message);
            $this->assertStringContainsString('svc_totp_forgot_2fa:', $message);
            $this->assertStringContainsString("policy: 'sliding_window'", $message);
            $this->assertStringContainsString('limit: 3', $message);
            $this->assertStringContainsString("interval: '15 minutes'", $message);
            $this->assertStringContainsString('SOLUTION:', $message);
            $this->assertStringContainsString('EXPLANATION:', $message);
        }
    }

    /**
     * Test that error message mentions the correct file paths.
     */
    public function testErrorMessageMentionsConfigurationFiles(): void
    {
        $container = new ContainerBuilder();

        $container->setDefinition(
            'Svc\TotpBundle\Controller\TotpForgotController',
            new Definition()
        );

        $pass = new RateLimiterValidationPass();

        try {
            $pass->process($container);
            $this->fail('Expected RuntimeException to be thrown');
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();

            // Verify the error message mentions configuration file locations
            $this->assertStringContainsString('config/packages/framework.yaml', $message);
            $this->assertStringContainsString('config/packages/rate_limiter.yaml', $message);
        }
    }
}
