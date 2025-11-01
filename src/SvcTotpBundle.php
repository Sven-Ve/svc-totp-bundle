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

namespace Svc\TotpBundle;

use Svc\TotpBundle\DependencyInjection\Compiler\RateLimiterValidationPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SvcTotpBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RateLimiterValidationPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
          ->children()
            ->stringNode('home_path')->cannotBeEmpty()->defaultValue('home')->info('Default Homepage path for redirecting after actions')->end()
            ->stringNode('loggingClass')
                ->defaultNull()
                ->info('Class to call for logging function. See doc for more information')
                ->example('App\Service\TotpLogger')
            ->end()
            ->booleanNode('enableForgot2FA')->defaultFalse()->info('Is "Forgot 2FA" functionality enabled?')->end()
            ->stringNode('fromEmail')
                ->defaultNull()
                ->info('Email address to use as sender for 2FA reset emails')
                ->example('no-reply@example.com')
            ->end()
          ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Validate configuration at compile time
        if ($config['enableForgot2FA']) {
            $fromEmail = $config['fromEmail'] ?? null;

            // Check if fromEmail is empty or whitespace-only
            if (empty($fromEmail) || empty(trim((string) $fromEmail))) {
                throw new \InvalidArgumentException('The "fromEmail" configuration parameter is required when "enableForgot2FA" is set to true. Please configure svc_totp.fromEmail in your bundle configuration (e.g., "no-reply@example.com").');
            }

            // Validate email format
            $trimmedEmail = trim((string) $fromEmail);
            if (!filter_var($trimmedEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('The "fromEmail" configuration parameter must be a valid email address. Provided value "' . $trimmedEmail . '" is not a valid email format. Please use a valid email address (e.g., "no-reply@example.com").');
            }
        }

        $container->import('../config/services.php');

        $container->services()
          ->get('Svc\TotpBundle\Controller\TotpController')
          ->arg(0, $config['home_path'])
          ->arg(1, $config['enableForgot2FA']);

        $container->services()
          ->get('Svc\TotpBundle\Controller\TotpForgotController')
          ->arg(0, $config['home_path'])
          ->arg(1, $config['enableForgot2FA'])
          ->arg('$fromEmail', $config['fromEmail']);

        if (array_key_exists('loggingClass', $config) && null !== $config['loggingClass']) {
            $builder->setAlias('Svc\TotpBundle\Service\TotpDefaultLogger', $config['loggingClass']);
        }
    }
}
