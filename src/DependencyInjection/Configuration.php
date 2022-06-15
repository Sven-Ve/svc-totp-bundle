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
      ->scalarNode('loggingClass')->defaultNull()->info('Class to call for logging function. See doc for more information')->end()
      ->booleanNode('enableForgot2FA')->defaultFalse()->info('Is "Forgot 2FA" functionality enabled?')->end()
    ->end();

    return $treeBuilder;
  }
}
