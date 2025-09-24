<?php

/*
 * This file is part of the SvcTotp bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\TotpBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SvcTotpBundle extends AbstractBundle
{
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
          ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->services()
          ->get('Svc\TotpBundle\Controller\TotpController')
          ->arg(0, $config['home_path'])
          ->arg(1, $config['enableForgot2FA']);

        $container->services()
          ->get('Svc\TotpBundle\Controller\TotpForgotController')
          ->arg(0, $config['home_path'])
          ->arg(1, $config['enableForgot2FA']);

        if (array_key_exists('loggingClass', $config) and null !== $config['loggingClass']) {
            $builder->setAlias('Svc\TotpBundle\Service\TotpDefaultLogger', $config['loggingClass']);
        }
    }
}
