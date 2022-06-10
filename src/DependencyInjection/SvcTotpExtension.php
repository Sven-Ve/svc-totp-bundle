<?php

namespace Svc\TotpBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SvcTotpExtension extends Extension
{
  public function load(array $configs, ContainerBuilder $container)
  {
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('services.xml');

    $configuration = $this->getConfiguration($configs, $container);
    $config = $this->processConfiguration($configuration, $configs);

    $definition = $container->getDefinition('svc_totp.controller');
    $definition->setArgument(0, $config['home_path']);

//    $definition = $container->getDefinition('svc_totp.service.logger');
//    $definition->setArgument(0, $config['loggingClass'] ?? null);

    if (null !== $config['loggingClass']) {
      $container->setAlias('svc_totp.service.default_logger', $config['loggingClass']);
    }
  }
}
