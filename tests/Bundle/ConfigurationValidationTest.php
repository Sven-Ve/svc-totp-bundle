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

namespace Svc\TotpBundle\Tests\Bundle;

use PHPUnit\Framework\TestCase;

class ConfigurationValidationTest extends TestCase
{
    /**
     * Test the configuration validation logic directly
     * This mirrors the validation in SvcTotpBundle::loadExtension().
     */
    private function validateConfiguration(array $config): void
    {
        if ($config['enableForgot2FA']) {
            $fromEmail = $config['fromEmail'] ?? null;

            // Check if fromEmail is empty or whitespace-only
            if (empty($fromEmail) || empty(trim((string) $fromEmail))) {
                throw new \InvalidArgumentException('The "fromEmail" configuration parameter is required when "enableForgot2FA" is set to true. Please configure svc_totp.fromEmail in your bundle configuration (e.g., "no-reply@example.com").');
            }

            // Validate email format
            $trimmedEmail = trim((string) $fromEmail);
            if (!filter_var($trimmedEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('The "fromEmail" configuration parameter must be a valid email address. Provided value "' . $trimmedEmail . '" is not a valid email format. Please use a valid email address (e.g., "no-reply@example.com").');
            }
        }
    }

    public function testValidConfigurationWithForgot2FAEnabled(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => 'test@example.com',
        ];

        // This should not throw an exception
        $this->validateConfiguration($config);

        // If we reach here, the test passed
        $this->assertTrue(true);
    }

    public function testValidConfigurationWithForgot2FADisabled(): void
    {
        $config = [
            'enableForgot2FA' => false,
            'fromEmail' => null,
        ];

        // This should not throw an exception even without fromEmail
        $this->validateConfiguration($config);

        // If we reach here, the test passed
        $this->assertTrue(true);
    }

    public function testValidConfigurationWithForgot2FADisabledButEmailSet(): void
    {
        $config = [
            'enableForgot2FA' => false,
            'fromEmail' => 'test@example.com',
        ];

        // This should not throw an exception
        $this->validateConfiguration($config);

        // If we reach here, the test passed
        $this->assertTrue(true);
    }

    public function testInvalidConfigurationMissingFromEmail(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => null,
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The "fromEmail" configuration parameter is required when "enableForgot2FA" is set to true'
        );

        $this->validateConfiguration($config);
    }

    public function testInvalidConfigurationEmptyFromEmail(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => '',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The "fromEmail" configuration parameter is required when "enableForgot2FA" is set to true'
        );

        $this->validateConfiguration($config);
    }

    public function testInvalidConfigurationWhitespaceFromEmail(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => '   ',
        ];

        // Whitespace-only strings should now be invalid after trim()
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The "fromEmail" configuration parameter is required when "enableForgot2FA" is set to true'
        );

        $this->validateConfiguration($config);
    }

    public function testInvalidConfigurationZeroStringFromEmail(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => '0',
        ];

        // '0' IS empty() in PHP, so this should trigger validation error
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The "fromEmail" configuration parameter is required when "enableForgot2FA" is set to true'
        );

        $this->validateConfiguration($config);
    }

    public function testExceptionMessageContainsHelpfulInformation(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => null,
        ];

        try {
            $this->validateConfiguration($config);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            // Check that the exception message contains helpful information
            $message = $e->getMessage();
            $this->assertStringContainsString('fromEmail', $message);
            $this->assertStringContainsString('enableForgot2FA', $message);
            $this->assertStringContainsString('svc_totp.fromEmail', $message);
            $this->assertStringContainsString('no-reply@example.com', $message);
        }
    }

    /**
     * Test edge cases with boolean values.
     */
    public function testEdgeCaseFalseAsFromEmail(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => false,
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->validateConfiguration($config);
    }

    /**
     * Test that the validation logic matches PHP's empty() function behavior.
     */
    public function testEmptyFunctionBehavior(): void
    {
        // Test that these values are considered empty and should trigger validation error
        foreach ([null, '', false, 0, '0', [], 0.0] as $emptyValue) {
            $config = [
                'enableForgot2FA' => true,
                'fromEmail' => $emptyValue,
            ];

            try {
                $this->validateConfiguration($config);
                $this->fail('Expected exception for empty value: ' . var_export($emptyValue, true));
            } catch (\InvalidArgumentException $e) {
                // Expected
                $this->assertTrue(true);
            }
        }

        // Test that valid email addresses are accepted
        foreach (['test@example.com', 'user.name@domain.co.uk', 'admin+test@example.org'] as $validEmail) {
            $config = [
                'enableForgot2FA' => true,
                'fromEmail' => $validEmail,
            ];

            $this->validateConfiguration($config);
            $this->assertTrue(true); // If we reach here, no exception was thrown
        }

        // Test that whitespace-only strings are now considered invalid
        foreach (['   ', "\t", "\n", " \t \n "] as $whitespaceValue) {
            $config = [
                'enableForgot2FA' => true,
                'fromEmail' => $whitespaceValue,
            ];

            try {
                $this->validateConfiguration($config);
                $this->fail('Expected exception for whitespace-only value: ' . var_export($whitespaceValue, true));
            } catch (\InvalidArgumentException $e) {
                // Expected
                $this->assertTrue(true);
            }
        }
    }

    public function testValidConfigurationWithEmailSurroundedByWhitespace(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => '  test@example.com  ',
        ];

        // Email surrounded by whitespace should be valid (trim() will clean it)
        $this->validateConfiguration($config);

        // If we reach here, the test passed
        $this->assertTrue(true);
    }

    public function testInvalidConfigurationVariousWhitespaceTypes(): void
    {
        $whitespaceTypes = [
            '   ',       // spaces
            "\t",        // tab
            "\n",        // newline
            "\r",        // carriage return
            " \t \n ",   // mixed
            "\r\n\t ",   // mixed with CRLF
        ];

        foreach ($whitespaceTypes as $whitespace) {
            $config = [
                'enableForgot2FA' => true,
                'fromEmail' => $whitespace,
            ];

            try {
                $this->validateConfiguration($config);
                $this->fail('Expected exception for whitespace: ' . var_export($whitespace, true));
            } catch (\InvalidArgumentException $e) {
                // Expected
                $this->assertStringContainsString(
                    'The "fromEmail" configuration parameter is required',
                    $e->getMessage()
                );
            }
        }
    }

    public function testInvalidEmailFormats(): void
    {
        $invalidEmails = [
            'not-an-email',
            'missing@',
            '@missing-local.com',
            'spaces @domain.com',
            'user@',
            'user@domain',
            'user..double@domain.com',
            '.user@domain.com',
            'user@.domain.com',
            'user@domain.',
            'user name@domain.com',
            'user@domain .com',
        ];

        foreach ($invalidEmails as $invalidEmail) {
            $config = [
                'enableForgot2FA' => true,
                'fromEmail' => $invalidEmail,
            ];

            try {
                $this->validateConfiguration($config);
                $this->fail('Expected exception for invalid email: ' . $invalidEmail);
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString(
                    'must be a valid email address',
                    $e->getMessage(),
                    'Wrong exception message for invalid email: ' . $invalidEmail
                );
                $this->assertStringContainsString(
                    $invalidEmail,
                    $e->getMessage(),
                    'Exception should contain the invalid email: ' . $invalidEmail
                );
            }
        }
    }

    public function testValidEmailFormats(): void
    {
        $validEmails = [
            'simple@example.com',
            'user.name@example.com',
            'user+tag@example.com',
            'user_name@example.com',
            'user123@example.com',
            'test@subdomain.example.com',
            'user@example.co.uk',
            'no-reply@example.org',
            'admin@test-domain.com',
            'info@123domain.com',
        ];

        foreach ($validEmails as $validEmail) {
            $config = [
                'enableForgot2FA' => true,
                'fromEmail' => $validEmail,
            ];

            $this->validateConfiguration($config);
            $this->assertTrue(true, 'Valid email should be accepted: ' . $validEmail);
        }
    }

    public function testEmailWithSurroundingWhitespaceIsValidated(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => '  valid@example.com  ',
        ];

        // Should pass validation after trimming
        $this->validateConfiguration($config);
        $this->assertTrue(true);
    }

    public function testEmailWithSurroundingWhitespaceButInvalidFormatFails(): void
    {
        $config = [
            'enableForgot2FA' => true,
            'fromEmail' => '  invalid-email  ',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a valid email address');
        $this->expectExceptionMessage('invalid-email');

        $this->validateConfiguration($config);
    }

    public function testNonStringEmailTypes(): void
    {
        // Test that non-string types are properly converted and validated
        $nonStringValues = [
            123,
            true,
            false,
            ['array'],
        ];

        foreach ($nonStringValues as $value) {
            $config = [
                'enableForgot2FA' => true,
                'fromEmail' => $value,
            ];

            try {
                // Suppress deprecation warnings for array-to-string conversion in PHP 8+
                @$this->validateConfiguration($config);
                $this->fail('Expected exception for non-string email: ' . var_export($value, true));
            } catch (\InvalidArgumentException $e) {
                // Should either fail at empty check or email format validation
                $message = $e->getMessage();
                $this->assertTrue(
                    str_contains($message, 'required when')
                    || str_contains($message, 'must be a valid email'),
                    'Unexpected exception message for value ' . var_export($value, true) . ': ' . $message
                );
            }
        }
    }
}
