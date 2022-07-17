<?php

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
        ->scalarNode('home_path')->cannotBeEmpty()->defaultValue('home')->info('Default Homepage path for redirecting after actions')->end()
        ->scalarNode('loggingClass')->defaultNull()->info('Class to call for logging function. See doc for more information')->end()
        ->booleanNode('enableForgot2FA')->defaultFalse()->info('Is "Forgot 2FA" functionality enabled?')->end()
      ->end();
  }

  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
  {
    $container->import('../config/services.yaml');

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
