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

namespace Svc\TotpBundle\Controller\Tests\Entity;

require_once __DIR__ . '/../Dummy/UserDummy.php';

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class MFATest extends TestCase
{
    public function testUserAddBackUpCode(): void
    {
        $user = new User();
        $this->assertTrue($user->addBackUpCode('123456'));
        $this->assertFalse($user->addBackUpCode('123456'));

        $user->clearBackUpCodes();
        $this->assertTrue($user->addBackUpCode('123456'));
    }

    public function testInvalidateBackupCode(): void
    {
        $user = new User();
        $this->assertTrue($user->addBackUpCode('123456'));
        $user->invalidateBackupCode('123456');
        $this->assertTrue($user->addBackUpCode('123456'));
    }

    public function testIsBackupCode(): void
    {
        $user = new User();
        $this->assertFalse($user->isBackupCode('123456'));

        $this->assertTrue($user->addBackUpCode('123456'));
        $this->assertTrue($user->isBackupCode('123456'));
        $this->assertFalse($user->isBackupCode('111111'));
    }

    public function testTrustedToken(): void
    {
        $user = new User();
        $this->assertEquals($user->getTrustedTokenVersion(), 0);

        $user->clearTrustedToken();
        $this->assertEquals($user->getTrustedTokenVersion(), 1);
    }

    public function testTOTPEnabled(): void
    {
        $user = new User();
        $user->setTotpSecret('abcdefg');
        $this->assertTrue($user->addBackUpCode('123456'));

        $this->assertTrue($user->enableTotpAuthentication());

        $this->assertTrue($user->isTotpSecret());
        $this->assertTrue($user->isTotpAuthenticationEnabled());
        $this->assertFalse($user->isBackupCode('123456'));
    }

    public function testTOTPDisabled(): void
    {
        $user = new User();
        $user->setTotpSecret('abcdefg');
        $this->assertTrue($user->addBackUpCode('123456'));

        $user->disableTotpAuthentication();

        $this->assertFalse($user->isTotpAuthenticationEnabled());
        $this->assertFalse($user->isBackupCode('123456'));
        $this->assertEquals($user->getTrustedTokenVersion(), 0);
    }

    public function testTOTPReset(): void
    {
        $user = new User();
        $user->setTotpSecret('abcdefg');
        $this->assertTrue($user->isTotpSecret());

        $user->disableTotpAuthentication(true);

        $this->assertFalse($user->isTotpAuthenticationEnabled());
        $this->assertFalse($user->isTotpSecret());
    }

    public function testTOTPUsername(): void
    {
        $user = new User();
        $user->setEmail('1@1.com');
        $this->assertEquals($user->getTotpAuthenticationUsername(), '1@1.com');
    }

    public function testGetMaxBackupCodes(): void
    {
        $user = new User();
        $this->assertEquals(10, $user->getMaxBackupCodes());
    }

    public function testMaxBackupCodesLimit(): void
    {
        $user = new User();

        // Add 10 backup codes (the maximum)
        for ($i = 1; $i <= 10; ++$i) {
            $code = str_pad((string) $i, 6, '0', STR_PAD_LEFT);
            $this->assertTrue($user->addBackUpCode($code), "Failed to add backup code $i");
        }

        // Try to add an 11th backup code - should fail
        $this->assertFalse($user->addBackUpCode('999999'), 'Should not allow adding more than 10 backup codes');
    }

    public function testGetTotpAuthenticationConfiguration(): void
    {
        $user = new User();
        $user->setTotpSecret('JBSWY3DPEHPK3PXP');

        $config = $user->getTotpAuthenticationConfiguration();

        $this->assertInstanceOf(\Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface::class, $config);
        $this->assertEquals('JBSWY3DPEHPK3PXP', $config->getSecret());
        $this->assertEquals(\Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration::ALGORITHM_SHA256, $config->getAlgorithm());
        $this->assertEquals(30, $config->getPeriod());
        $this->assertEquals(6, $config->getDigits());
    }

    public function testSetTotpSecret(): void
    {
        $user = new User();
        $this->assertFalse($user->isTotpSecret());

        $user->setTotpSecret('TESTSECRET123');
        $this->assertTrue($user->isTotpSecret());

        $user->setTotpSecret(null);
        $this->assertFalse($user->isTotpSecret());
    }

    public function testEnableTotpAuthenticationWithoutSecret(): void
    {
        $user = new User();
        $this->assertFalse($user->enableTotpAuthentication(), 'Should not enable TOTP without a secret');
        $this->assertFalse($user->isTotpAuthenticationEnabled());
    }

    public function testBackupCodesNullSafety(): void
    {
        $user = new User();

        // Test isBackupCode with null backup codes array
        $this->assertFalse($user->isBackupCode('123456'));

        // Add a backup code to initialize the array
        $this->assertTrue($user->addBackUpCode('123456'));
        $this->assertTrue($user->isBackupCode('123456'));
    }
}
