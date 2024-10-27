<?php

namespace Svc\TotpBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Endroid\QrCodeBundle\EndroidQrCodeBundle;
use Svc\TotpBundle\SvcTotpBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use SymfonyCasts\Bundle\VerifyEmail\SymfonyCastsVerifyEmailBundle;

class SvcTotpTestingKernel extends Kernel
{
  private $builder;

  public function registerBundles(): iterable
  {
    return [
      new SvcTotpBundle(),
      new FrameworkBundle(),
      new DoctrineBundle(),
      new SymfonyCastsVerifyEmailBundle(),
      new EndroidQrCodeBundle(),
    ];
  }

  public function registerContainerConfiguration(LoaderInterface $loader): void
  {
    if (null === $this->builder) {
      $this->builder = new ContainerBuilder();
    }

    $builder = $this->builder;

    $loader->load(function (ContainerBuilder $container) use ($builder) {
      $container->merge($builder);

      $container->loadFromExtension(
        'framework',
        [
          'secret' => 'foo',
          'http_method_override' => false,
          'router' => [
            'resource' => 'kernel::loadRoutes',
            'type' => 'service',
            'utf8' => true,
          ],
        ]
      );

      $container->loadFromExtension('doctrine', [
        'dbal' => [
          //          'override_url' => true,
          'driver' => 'pdo_sqlite',
          'url' => 'sqlite:///' . $this->getCacheDir() . '/app.db',
        ],
        'orm' => [
          'auto_generate_proxy_classes' => true,
          'auto_mapping' => true,
        ],
      ]);

    });
  }
}
