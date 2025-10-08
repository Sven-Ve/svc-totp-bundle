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

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;

trait _TotpTrait
{
    #[ORM\Column(nullable: true)]
    private ?string $totpSecret = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isTotpAuthenticationEnabled = false;

    #[ORM\Column(options: ['default' => 0])]
    private int $trustedVersion = 0;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private ?array $backupCodes = [];

    public function isTotpSecret(): bool
    {
        return $this->totpSecret ? true : false;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->isTotpAuthenticationEnabled and ($this->totpSecret ? true : false);
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface
    {
        // You could persist the other configuration options in the user entity to make it individual per user.
        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA256, 30, 6);
        //        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    /**
     * set the TOTP secret.
     */
    public function setTotpSecret(?string $totpSecret): self
    {
        $this->totpSecret = $totpSecret;

        return $this;
    }

    /**
     * enable TOTP.
     */
    public function enableTotpAuthentication(): bool
    {
        if ($this->totpSecret) {
            $this->isTotpAuthenticationEnabled = true;
            $this->clearBackUpCodes();

            return true;
        }

        return false;

    }

    /**
     * disable TOTP completely.
     */
    public function disableTotpAuthentication(?bool $reset = false): void
    {
        $this->isTotpAuthenticationEnabled = false;
        $this->trustedVersion = 0;
        $this->clearBackUpCodes();
        if ($reset) {
            $this->setTotpSecret(null);
        }
    }

    /**
     * get the version of the trusted token.
     */
    public function getTrustedTokenVersion(): int
    {
        return $this->trustedVersion;
    }

    /**
     * clear all trusted tokens for this user (inc trustedVersion).
     */
    public function clearTrustedToken(): void
    {
        ++$this->trustedVersion;
    }

    /**
     * Check if it is a valid backup code.
     */
    public function isBackupCode(string $code): bool
    {
        if ($this->backupCodes === null) {
            return false;
        }

        return in_array($code, $this->backupCodes);
    }

    /**
     * Invalidate a backup code.
     */
    public function invalidateBackupCode(string $code): void
    {
        $key = array_search($code, $this->backupCodes);
        if ($key !== false) {
            unset($this->backupCodes[$key]);
        }
    }

    /**
     * Add a backup code, return true, if successfull.
     */
    public function addBackUpCode(string $backUpCode): bool
    {
        if ($this->backupCodes === null) {
            $this->backupCodes = [];
        }

        if (count($this->backupCodes) >= $this->getMaxBackupCodes()) {
            return false;
        }

        if (!in_array($backUpCode, $this->backupCodes)) {
            $this->backupCodes[] = $backUpCode;

            return true;
        }

        return false;
    }

    /**
     * clear all backup codes.
     */
    public function clearBackUpCodes(): void
    {
        $this->backupCodes = [];
    }

    /**
     * get max number of backupcodes.
     */
    public function getMaxBackupCodes(): int
    {
        return 10;
    }
}
