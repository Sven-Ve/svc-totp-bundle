<?php

/*
 * This file is part of the SvcTotp bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\TotpBundle\Service;

/**
 * Interface for the log provider.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
interface TotpLoggerInterface
{
    public const LOG_TOTP_SHOW_QR = 1;
    public const LOG_TOTP_ENABLE = 2;
    public const LOG_TOTP_DISABLE = 3;
    public const LOG_TOTP_RESET = 4;
    public const LOG_TOTP_CLEAR_TD = 5;
    public const LOG_TOTP_DISABLE_BY_ADMIN = 6;
    public const LOG_TOTP_RESET_BY_ADMIN = 7;
    public const LOG_TOTP_CLEAR_TD_BY_ADMIN = 8;

    /**
     * log a message.
     */
    public function log(string $text, int $logType, int $userId): bool;
}
