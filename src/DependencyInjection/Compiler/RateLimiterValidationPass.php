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

namespace Svc\TotpBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Validates that the rate limiter service is configured when Forgot 2FA is enabled.
 */
class RateLimiterValidationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Only validate if the TotpForgotController service exists
        if (!$container->hasDefinition('Svc\TotpBundle\Controller\TotpForgotController')) {
            return;
        }

        // Check if the rate limiter service exists
        if (!$container->has('limiter.svc_totp_forgot_2fa')) {
            $this->throwConfigurationError();
        }
    }

    private function throwConfigurationError(): void
    {
        $message = <<<'ERROR'

╔════════════════════════════════════════════════════════════════════════════════╗
║                                                                                ║
║  SvcTotpBundle Configuration Error: Rate Limiter Not Configured               ║
║                                                                                ║
╚════════════════════════════════════════════════════════════════════════════════╝

The "Forgot 2FA" functionality requires rate limiting to be configured, but the
rate limiter service "limiter.svc_totp_forgot_2fa" was not found.

SOLUTION:
─────────
Add the following configuration to your application:

File: config/packages/framework.yaml (or config/packages/rate_limiter.yaml)

    framework:
        rate_limiter:
            svc_totp_forgot_2fa:
                policy: 'sliding_window'
                limit: 3
                interval: '15 minutes'

EXPLANATION:
────────────
This rate limiter prevents abuse of the "Forgot 2FA" email functionality by
limiting requests to 3 per 15 minutes per IP address.

You can customize the settings:
  - policy: 'sliding_window', 'fixed_window', or 'token_bucket'
  - limit: Number of allowed requests (recommended: 3-5)
  - interval: Time window (e.g., '10 minutes', '1 hour')

For more information, see:
https://github.com/Sven-Ve/svc-totp-bundle/blob/main/docs/config.md#rate-limiting-configuration-version-660

ERROR;

        throw new \RuntimeException($message);
    }
}
