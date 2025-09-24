<?php

/*
 * This file is part of the SvcTotp bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Svc\TotpBundle\Controller\TotpAdminController;
use Svc\TotpBundle\Controller\TotpController;
use Svc\TotpBundle\Controller\TotpForgotController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('svc_totp_manage', '/manage/')
        ->controller([TotpController::class, 'manageTotp']);

    $routes->add('svc_totp_qrcode', '/qrcode/')
        ->controller([TotpController::class, 'totpQRCode']);

    $routes->add('svc_totp_enable', '/enable/')
        ->controller([TotpController::class, 'enableTotp']);

    $routes->add('svc_totp_disable', '/disable/')
        ->controller([TotpController::class, 'disableTotp']);

    $routes->add('svc_totp_oth_disable', '/disable/{id}')
        ->controller([TotpController::class, 'disableOtherTotp']);

    $routes->add('svc_totp_cleartd', '/cleartd/')
        ->controller([TotpController::class, 'clearTrustedDevice']);

    $routes->add('svc_totp_clear_oth_td', '/clearotd/{id}')
        ->controller([TotpController::class, 'clearOtherTrustedDevice']);

    $routes->add('svc_totp_user_admin', '//admin/users/')
        ->controller([TotpAdminController::class, 'index']);

    $routes->add('svc_totp_forgot', '/forgot/')
        ->controller([TotpForgotController::class, 'forgetPassword']);

    $routes->add('svc_totp_verify_forgot', '/forgot/verify/')
        ->controller([TotpForgotController::class, 'verifyForgetPassword']);

    $routes->add('svc_totp_forgot_btn', '/forgot/btn/')
        ->controller([TotpController::class, 'forgotButton']);
};
