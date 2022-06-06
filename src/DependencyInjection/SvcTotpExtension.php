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
    /*
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('svc_totp.twig_runtime');
        $definition->setArgument(0, $config['size'] ?? 32);
        $definition->setArgument(1, $config['backgroundcolor'] ?? 'random');
        $definition->setArgument(2, $config['fontcolor'] ?? null);
        $definition->setArgument(3, $config['rounded'] ?? false);
        $definition->setArgument(4, $config['bold'] ?? false);
        $definition->setArgument(5, $config['retina'] ?? true);
        */
  }
}
