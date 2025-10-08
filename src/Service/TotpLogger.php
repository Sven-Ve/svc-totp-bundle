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

class TotpLogger
{
    public function __construct(private readonly TotpLoggerInterface $logger, private readonly ?string $env)
    {
    }

    public function log(string $text, int $logType, int $userId): bool
    {
        try {
            $this->logger->log($text, $logType, $userId);

            return true;
        } catch (\ArgumentCountError|\Exception $e) {
            // Always log the exception to PHP error log for debugging
            error_log(sprintf(
                '[SvcTotpBundle] Logger exception: %s in %s:%d - Message: %s',
                get_class($e),
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ));

            if ($this->env === 'dev') {
                throw $e;
            }

            return false;
        }
    }
}
