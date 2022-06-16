<?php

namespace Svc\TotpBundle\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Svc\TotpBundle\Service\TotpLogger;
use Svc\TotpBundle\Service\TotpLoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TotpController extends AbstractController
{
  public function __construct(private readonly string $homePath, private readonly bool $enableForgot2FA, private readonly TotpLogger $logger, private readonly EntityManagerInterface $entityManager, private readonly TranslatorInterface $translator)
  {
  }

  /**
   * manage the 2fa (enable, disable, backup codes).
   */
  public function manageTotp(TotpAuthenticatorInterface $totpAuthenticator, SessionInterface $session): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $user = $this->getUser();

    if ($session->get('genBackupCodes')) {
      $session->remove('genBackupCodes');

      $this->logger->log('New backup codes generated', TotpLoggerInterface::LOG_TOTP_SHOW_QR, $user->getId());

      return $this->render('@SvcTotp/totp/backCodesTotp.html.twig', [
        'backupcodes' => $this->generateBackCodes(),
      ]);
    }

    if (!$user->isTotpSecret()) {
      $user->setTotpSecret($totpAuthenticator->generateSecret());
      $this->entityManager->flush();
      $this->logger->log('New QR code generated.', TotpLoggerInterface::LOG_TOTP_SHOW_QR, $user->getId());
    }

    $this->logger->log('Called TOTP manage page, show QR code', TotpLoggerInterface::LOG_TOTP_SHOW_QR, $user->getId());

    return $this->render('@SvcTotp/totp/manageTotp.html.twig');
  }

  /**
   * show the qr code for the current user.
   */
  public function totpQRCode(TotpAuthenticatorInterface $totpAuthenticator): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $user = $this->getUser();
    if (!$user->isTotpSecret()) {
      return new Response();
    }

    $result = Builder::create()
      /* @phpstan-ignore-next-line */
      ->data($totpAuthenticator->getQRContent($user))
      ->size(200)
      ->margin(0)
      ->build();

    return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
  }

  /**
   * enable the qr code.
   */
  public function enableTotp(SessionInterface $session): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    $user = $this->getUser();
    if ($user->isTotpSecret()) {
      $user->enableTotpAuthentication();
      $this->entityManager->flush();
      $session->set('genBackupCodes', true);
      $this->logger->log('TOTP enabled', TotpLoggerInterface::LOG_TOTP_ENABLE, $user->getId());
    } else {
      $this->addFlash('warning', 'Cannot enable 2FA');
    }

    return $this->redirectToRoute('svc_totp_manage');
  }

  /**
   * disable/reset 2fa  for the current user.
   */
  public function disableTotp(Request $request): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $reset = (bool) $request->get('reset');
    $user = $this->getUser();
    if ($user->isTotpAuthenticationEnabled()) {
      $user->disableTotpAuthentication($reset);
      $this->entityManager->flush();
      if ($reset) {
        $this->logger->log('TOTP reset', TotpLoggerInterface::LOG_TOTP_RESET, $user->getId());
      } else {
        $this->logger->log('TOTP disabled', TotpLoggerInterface::LOG_TOTP_DISABLE, $user->getId());
      }
    }

    return $this->redirectToRoute('svc_totp_manage');
  }

  /**
   * disable/reset  2fa for another user.
   */
  public function disableOtherTotp(User $user, Request $request): Response
  {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $reset = (bool) $request->get('reset');

    if ($user->isTotpAuthenticationEnabled()) {
      $user->disableTotpAuthentication($reset);
      $this->entityManager->flush();
      if ($reset) {
        $this->logger->log('TOTP reset by ' . $this->getUser()->getUserIdentifier(), TotpLoggerInterface::LOG_TOTP_RESET_BY_ADMIN, $user->getId());
        $this->addFlash('info', '2FA for user ' . $user->getUserIdentifier() . ' reset.');
      } else {
        $this->logger->log('TOTP disabled by ' . $this->getUser()->getUserIdentifier(), TotpLoggerInterface::LOG_TOTP_DISABLE_BY_ADMIN, $user->getId());
        $this->addFlash('info', '2FA for user ' . $user->getUserIdentifier() . ' disabled.');
      }
    }

    return $this->redirectToRoute($this->homePath);
  }

  /**
   * clear trusted device for current or all users.
   */
  public function clearTrustedDevice(UserRepository $userRep, Request $request): Response
  {
    $allUsers = (bool) $request->get('allUsers');

    if (!$allUsers) {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      $user = $this->getUser();
      $user->clearTrustedToken();
      $this->entityManager->flush();
      $this->addFlash('info', $this->t('Your trusted devices have been deleted.'));
      $this->logger->log('TOTP trusted devices cleared', TotpLoggerInterface::LOG_TOTP_CLEAR_TD, $user->getId());

      return $this->redirectToRoute('svc_totp_manage');
    } else {
      $this->denyAccessUnlessGranted('ROLE_ADMIN');

      /* @phpstan-ignore-next-line */
      foreach ($userRep->findBy(['isTotpAuthenticationEnabled' => true]) as $user) {
        $user->clearTrustedToken();
        $this->logger->log('TOTP trusted devices (all) cleared by ' . $this->getUser()->getUserIdentifier(), TotpLoggerInterface::LOG_TOTP_CLEAR_TD_BY_ADMIN, $user->getId());
      }
      $this->entityManager->flush();
      $this->addFlash('info', $this->t('All trusted devices have been deleted.'));

      return $this->redirectToRoute($this->homePath);
    }
  }

  /**
   * clear trusted device for other users.
   */
  public function clearOtherTrustedDevice(User $user): Response
  {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $user->clearTrustedToken();
    $this->entityManager->flush();

    $this->logger->log('TOTP trusted devices cleared by ' . $this->getUser()->getUserIdentifier(), TotpLoggerInterface::LOG_TOTP_CLEAR_TD_BY_ADMIN, $user->getId());
    $this->addFlash('info', $this->t('The trusted devices for user %user% have been deleted.', ['%user%'=>$user->getUserIdentifier()]));

    return $this->redirectToRoute($this->homePath);
  }

  /**
   * show the button "forgot 2FA" if function enabled.
   */
  public function forgotButton(): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_2FA_IN_PROGRESS');

    if (!$this->enableForgot2FA) {
      return new Response();
    }

    return $this->render('@SvcTotp/forgot/_forgot2FAbtn.html.twig');
  }

  /**
   * generate a backup code with $digits digits (default 6).
   */
  private function generateCode(int $digits = 6): int
  {
    $min = 10 ** ($digits - 1);
    $max = 10 ** $digits - 1;

    return random_int($min, $max);
  }

  /**
   * create an array of backup codes
   */
  private function generateBackCodes(): array
  {
    $user = $this->getUser();
    if ($user->isTotpSecret()) {
      $user->clearBackUpCodes();
      $bCodes = [];
      while (count($bCodes) < $user->getMaxBackupCodes()) {
        $bCode = $this->generateCode();
        if ($user->addBackUpCode((string) $bCode)) {
          $bCodes[] = $bCode;
        }
      }

      $this->entityManager->flush();

      return $bCodes;
    } else {
      return [];
    }
  }

  /**
   * private function to translate content in namespace 'TotpBundle'.
   */
  private function t(string $text, array $placeholder = []): string
  {
    return $this->translator->trans($text, $placeholder, 'TotpBundle');
  }
}
