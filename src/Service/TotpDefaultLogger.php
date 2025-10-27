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

namespace Svc\TotpBundle\Service;

class TotpDefaultLogger implements TotpLoggerInterface
{
    public function log(string $text, int $logType, int $userId): bool
    {
        // do nothing...
        return true;
    }
}
