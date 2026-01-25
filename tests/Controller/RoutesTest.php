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

/**
 * Tests for route configurations and HTTP method restrictions.
 */
class RoutesTest extends TestCase
{
    /**
     * Test that route configuration is valid and contains expected routes.
     */
    public function testRouteConfigurationExists(): void
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        $this->assertFileExists($routesFile, 'Routes configuration file should exist');

        $routesContent = file_get_contents($routesFile);
        $this->assertNotFalse($routesContent, 'Should be able to read routes file');

        // Verify that key routes are defined
        $this->assertStringContainsString('svc_totp_manage', $routesContent);
        $this->assertStringContainsString('svc_totp_enable', $routesContent);
        $this->assertStringContainsString('svc_totp_disable', $routesContent);
        $this->assertStringContainsString('svc_totp_forgot', $routesContent);
    }

    /**
     * Test that state-changing routes have POST method restrictions.
     */
    public function testStateChangingRoutesRequirePost(): void
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        $routesContent = file_get_contents($routesFile);
        $this->assertNotFalse($routesContent);

        // Parse routes and verify POST methods on state-changing operations
        $stateChangingRoutes = [
            'svc_totp_enable',
            'svc_totp_disable',
            'svc_totp_oth_disable',
            'svc_totp_cleartd',
            'svc_totp_clear_oth_td',
        ];

        foreach ($stateChangingRoutes as $route) {
            $this->assertStringContainsString($route, $routesContent);

            // Find the route definition and check for methods restriction
            $pattern = '/\'' . preg_quote($route, '/') . '\'.*?->methods\(\[\'POST\'\]\)/s';
            $this->assertMatchesRegularExpression(
                $pattern,
                $routesContent,
                "Route '{$route}' should be restricted to POST method only"
            );
        }
    }

    /**
     * Test that read-only routes have GET method restrictions.
     */
    public function testReadOnlyRoutesRequireGet(): void
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        $routesContent = file_get_contents($routesFile);
        $this->assertNotFalse($routesContent);

        $readOnlyRoutes = [
            'svc_totp_manage',
            'svc_totp_qrcode',
            'svc_totp_user_admin',
            'svc_totp_forgot_btn',
            'svc_totp_verify_forgot',
        ];

        foreach ($readOnlyRoutes as $route) {
            $this->assertStringContainsString($route, $routesContent);

            // Verify GET method is allowed
            $pattern = '/\'' . preg_quote($route, '/') . '\'.*?->methods\(\[\'GET\'\]\)/s';
            $this->assertMatchesRegularExpression(
                $pattern,
                $routesContent,
                "Route '{$route}' should allow GET method"
            );
        }
    }

    /**
     * Test that forgot 2FA route allows both GET and POST.
     */
    public function testForgotRouteAllowsGetAndPost(): void
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        $routesContent = file_get_contents($routesFile);
        $this->assertNotFalse($routesContent);

        // The forgot route should accept both GET (to show form) and POST (to submit)
        $pattern = '/svc_totp_forgot\'.*?->methods\(\[\'GET\', \'POST\'\]\)/s';
        $this->assertMatchesRegularExpression(
            $pattern,
            $routesContent,
            'Forgot 2FA route should allow both GET and POST methods'
        );
    }

    /**
     * Test that all routes have proper controller mappings.
     */
    public function testAllRoutesHaveControllers(): void
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        $routesContent = file_get_contents($routesFile);
        $this->assertNotFalse($routesContent);

        // Check that controller classes are referenced
        $this->assertStringContainsString('TotpController::class', $routesContent);
        $this->assertStringContainsString('TotpAdminController::class', $routesContent);
        $this->assertStringContainsString('TotpForgotController::class', $routesContent);
    }
}
