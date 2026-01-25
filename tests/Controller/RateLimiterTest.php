<?php

declare(strict_types=1);

/*
 * This file is part of the SvcTotp bundle.
 *
 * (c) 2026 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\TotpBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests for rate limiter configuration.
 */
class RateLimiterTest extends TestCase
{
    /**
     * Test that rate limiter configuration file exists.
     */
    public function testRateLimiterConfigurationExists(): void
    {
        $configFile = __DIR__ . '/../../config/packages/rate_limiter.yaml';
        $this->assertFileExists($configFile, 'Rate limiter configuration file should exist');
    }

    /**
     * Test that rate limiter configuration is valid YAML.
     */
    public function testRateLimiterConfigurationIsValidYaml(): void
    {
        $configFile = __DIR__ . '/../../config/packages/rate_limiter.yaml';
        $content = file_get_contents($configFile);
        $this->assertNotFalse($content);

        $config = Yaml::parse($content);
        $this->assertIsArray($config, 'Configuration should be a valid YAML array');
    }

    /**
     * Test that rate limiter for forgot 2FA is configured.
     */
    public function testForgot2FALimiterIsConfigured(): void
    {
        $configFile = __DIR__ . '/../../config/packages/rate_limiter.yaml';
        $content = file_get_contents($configFile);
        $this->assertNotFalse($content);

        $config = Yaml::parse($content);

        $this->assertArrayHasKey('framework', $config);
        $this->assertArrayHasKey('rate_limiter', $config['framework']);
        $this->assertArrayHasKey('svc_totp_forgot_2fa', $config['framework']['rate_limiter']);

        $limiterConfig = $config['framework']['rate_limiter']['svc_totp_forgot_2fa'];

        // Verify rate limiter settings
        $this->assertEquals('sliding_window', $limiterConfig['policy']);
        $this->assertEquals(3, $limiterConfig['limit']);
        $this->assertEquals('15 minutes', $limiterConfig['interval']);
    }

    /**
     * Test that rate limiter configuration has reasonable limits.
     */
    public function testRateLimiterHasReasonableLimits(): void
    {
        $configFile = __DIR__ . '/../../config/packages/rate_limiter.yaml';
        $content = file_get_contents($configFile);
        $this->assertNotFalse($content);

        $config = Yaml::parse($content);
        $limiterConfig = $config['framework']['rate_limiter']['svc_totp_forgot_2fa'];

        // Verify that limits are not too permissive
        $this->assertLessThanOrEqual(10, $limiterConfig['limit'], 'Rate limit should not exceed 10 requests');
        $this->assertGreaterThanOrEqual(1, $limiterConfig['limit'], 'Rate limit should be at least 1 request');

        // Verify that interval is reasonable (not too short)
        $this->assertStringContainsString('minute', $limiterConfig['interval'], 'Interval should be in minutes');
    }

    /**
     * Test that rate limiter uses secure policy.
     */
    public function testRateLimiterUsesSecurePolicy(): void
    {
        $configFile = __DIR__ . '/../../config/packages/rate_limiter.yaml';
        $content = file_get_contents($configFile);
        $this->assertNotFalse($content);

        $config = Yaml::parse($content);
        $limiterConfig = $config['framework']['rate_limiter']['svc_totp_forgot_2fa'];

        // Verify policy is one of the recommended ones
        $allowedPolicies = ['sliding_window', 'fixed_window', 'token_bucket'];
        $this->assertContains(
            $limiterConfig['policy'],
            $allowedPolicies,
            'Rate limiter should use a recognized policy'
        );
    }
}
