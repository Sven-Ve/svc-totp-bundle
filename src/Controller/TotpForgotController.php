<?php

declare(strict_types=1);

/*
 * This file is part of the SvcTotp bundle.
 *
 * (c) 2026 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\TotpBundle\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Svc\TotpBundle\Service\TotpLogger;
use Svc\TotpBundle\Service\TotpLoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class TotpForgotController extends AbstractController
{
    public function __construct(
        private readonly string $homePath,
        private readonly bool $enableForgot2FA,
        private readonly TotpLogger $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly TranslatorInterface $translator,
        private readonly ?string $fromEmail,
        private readonly RateLimiterFactory $svcTotpForgot2faLimiter,
    ) {
    }

    /**
     * forget password, reset via mail.
     */
    public function forgetPassword(Request $request, MailerInterface $mailer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_2FA_IN_PROGRESS');

        if (!$this->enableForgot2FA) {
            $this->addFlash('warning', 'Forgot 2FA function is not enabled.');

            return $this->redirectToRoute($this->homePath);
        }

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('User not authenticated');
        }

        $send = (bool) $request->request->get('send', false);

        // Only validate CSRF token and apply rate limiting when actually sending
        if ($send) {
            // CSRF validation
            $csrfToken = $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('totp-forgot', is_string($csrfToken) ? $csrfToken : null)) {
                $this->addFlash('error', $this->t('Invalid CSRF token. Please try again.'));

                return $this->redirectToRoute($this->homePath);
            }

            // Rate limiting: Check if user has exceeded the limit for forgot 2FA requests
            $limiter = $this->svcTotpForgot2faLimiter->create($request->getClientIp());
            if (!$limiter->consume(1)->isAccepted()) {
                $this->addFlash('error', $this->t('Too many requests. Please try again later.'));

                return $this->redirectToRoute($this->homePath);
            }
        }

        if ($send) {
            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'svc_totp_verify_forgot',
                (string) $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            // prepare email
            $email = new TemplatedEmail();
            $email->from($this->fromEmail);
            $email->to($user->getEmail());
            $email->subject($this->t('Reset 2FA'));
            $email->priority(Email::PRIORITY_HIGH);
            $email->htmlTemplate('@SvcTotp/forgot/verify_email.html.twig');
            $email->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
            ]);

            $mailer->send($email);
            $this->addFlash('info', $this->t('OTP reset email sent, please check your inbox'));

            return $this->redirectToRoute('app_logout');
        }

        return $this->render('@SvcTotp/forgot/forget2FA.html.twig');
    }

    /**
     * verify forget password, reset via mail.
     */
    public function verifyForgetPassword(
        #[MapQueryParameter] ?int $id,
        Request $request,
        UserRepository $userRep,
    ): Response {
        if (!$this->enableForgot2FA) {
            $this->addFlash('warning', 'Forgot 2FA function is not enabled.');

            return $this->redirectToRoute($this->homePath);
        }

        // Validate that ID is a positive integer
        if (null === $id || $id <= 0) {
            $this->addFlash('error', $this->t('This reset link is invalid. Please request a new one.'));

            return $this->redirectToRoute($this->homePath);
        }

        $user = $userRep->find($id);

        // Ensure the user exists in persistence
        if (null === $user) {
            $this->addFlash('error', $this->t('This reset link is invalid or has expired.'));

            return $this->redirectToRoute($this->homePath);
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $user->getEmail());

        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());

            return $this->redirectToRoute($this->homePath);
        }

        // reset 2FA
        $user->disableTotpAuthentication();
        $this->entityManager->flush();
        $this->logger->log('TOTP disabled by forget function', TotpLoggerInterface::LOG_TOTP_RESET, $user->getId());
        $this->addFlash('success', $this->t('2FA is disabled'));

        return $this->redirectToRoute('app_logout');
    }

    /**
     * private function to translate content in namespace 'TotpBundle'.
     *
     * @param array<string, mixed> $placeholder
     */
    private function t(string $text, array $placeholder = []): string
    {
        return $this->translator->trans($text, $placeholder, 'TotpBundle');
    }
}
