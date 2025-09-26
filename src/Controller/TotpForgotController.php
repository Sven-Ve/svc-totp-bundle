<?php

/*
 * This file is part of the SvcTotp bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class TotpForgotController extends AbstractController
{
    public function __construct(private readonly string $homePath, private readonly bool $enableForgot2FA, private readonly TotpLogger $logger, private readonly EntityManagerInterface $entityManager, private readonly VerifyEmailHelperInterface $verifyEmailHelper, private readonly TranslatorInterface $translator, private readonly ?string $fromEmail)
    {
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
        $send = (bool) $request->get('send', false);

        if ($send) {
            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'svc_totp_verify_forgot',
                $user->getId(),
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
    public function verifyForgetPassword(Request $request, UserRepository $userRep): Response
    {
        if (!$this->enableForgot2FA) {
            $this->addFlash('warning', 'Forgot 2FA function is not enabled.');

            return $this->redirectToRoute($this->homePath);
        }
        $id = $request->get('id');

        if (null === $id) {
            $this->addFlash('danger', 'No user defined.');

            return $this->redirectToRoute($this->homePath);
        }

        $user = $userRep->find($id);

        // Ensure the user exists in persistence
        if (null === $user) {
            $this->addFlash('danger', 'User not exists.');

            return $this->redirectToRoute($this->homePath);
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('danger', $e->getReason());

            return $this->redirectToRoute($this->homePath);
        }

        // reset 2FA
        $user->disableTotpAuthentication();
        $this->entityManager->flush();
        $this->logger->log('TOTP disabled by forget function', TotpLoggerInterface::LOG_TOTP_RESET, $user->getId());

        return $this->redirectToRoute('app_logout');
    }

    /**
     * private function to translate content in namespace 'TotpBundle'.
     */
    private function t(string $text, array $placeholder = []): string
    {
        return $this->translator->trans($text, $placeholder, 'TotpBundle');
    }
}
