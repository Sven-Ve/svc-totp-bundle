<?php

namespace Svc\TotpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
  public function getConfigTreeBuilder(): TreeBuilder
  {
    $treeBuilder = new TreeBuilder('svc_totp');
    $rootNode = $treeBuilder->getRootNode();

    $rootNode
    ->children()
      ->scalarNode('home_path')->cannotBeEmpty()->defaultValue('home')->info('Default Homepage path for redirecting after actions')->end()
    ->end();

    return $treeBuilder;
  }
}
