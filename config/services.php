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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Svc\TotpBundle\Controller\TotpAdminController;
use Svc\TotpBundle\Controller\TotpController;
use Svc\TotpBundle\Controller\TotpForgotController;
use Svc\TotpBundle\Service\TotpDefaultLogger;
use Svc\TotpBundle\Service\TotpLogger;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private();

    $services->set(TotpController::class);
    $services->set(TotpAdminController::class);
    $services->set(TotpForgotController::class);

    $services->set(TotpLogger::class)
        ->args([
            service(TotpDefaultLogger::class),
            param('kernel.environment'),
        ])
        ->public();

    $services->set(TotpDefaultLogger::class)
        ->public();

};
