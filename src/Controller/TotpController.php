<?php

namespace Svc\TotpBundle\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TotpController extends AbstractController
{
  public function __construct(private readonly EntityManagerInterface $entityManager)
  {
  }

  /**
   * manage the 2fa (enable, disable, backup codes).
   */
  #[Route(path: '/authentication/totp/manage/{_locale}', name: 'app_totp_manage', requirements: ['_locale' => '%app.supported_locales%'])]
  public function manageTotp(TotpAuthenticatorInterface $totpAuthenticator, SessionInterface $session): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $user = $this->getUser();

    if ($session->get('genBackupCodes')) {
      $session->remove('genBackupCodes');

      return $this->render('mfa/backCodesTotp.html.twig', [
        'backupcodes' => $this->generateBackCodes(),
      ]);
    }

    if (!$user->isTotpSecret()) {
      $user->setTotpSecret($totpAuthenticator->generateSecret());
      $this->entityManager->flush();
    }

    return $this->render('mfa/manageTotp.html.twig');
  }

  /**
   * show the qr code for the current user.
   */
  #[Route(path: '/authentication/totp/qrcode/{_locale}', name: 'app_totp_qrcode', requirements: ['_locale' => '%app.supported_locales%'])]
  public function totpQRCode(TotpAuthenticatorInterface $totpAuthenticator): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $user = $this->getUser();
    if (!$user->isTotpSecret()) {
      return new Response();
    }

    $result = Builder::create()
      ->data($totpAuthenticator->getQRContent($user))
      ->size(200)
      ->margin(0)
      ->build();

    return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
  }

  /**
   * enable the qr code.
   */
  #[Route(path: '/authentication/totp/enable/{_locale}', name: 'app_totp_enable', requirements: ['_locale' => '%app.supported_locales%'])]
  public function enableTotp(SessionInterface $session): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    $user = $this->getUser();
    if ($user->isTotpSecret()) {
      $user->enableTotpAuthentication();
      $this->entityManager->flush();
      $session->set('genBackupCodes', true);

      return $this->redirectToRoute('app_totp_manage');
    //      return $this->redirectToRoute('app_totp_backcode');
    } else {
      $this->addFlash('warning', 'Cannot enable 2FA');

      return $this->redirectToRoute('app_totp_manage');
    }
  }

  /**
   * disable 2fa but keep the secret.
   */
  #[Route(path: '/authentication/totp/disable/{_locale}', name: 'app_totp_disable', requirements: ['_locale' => '%app.supported_locales%'])]
  public function disableTotp(): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $user = $this->getUser();
    if ($user->isTotpAuthenticationEnabled()) {
      $user->disableTotpAuthentication();
      $this->entityManager->flush();
    }

    return $this->redirectToRoute('app_totp_manage');
  }

  /**
   * clear trusted device for current or all users.
   */
  #[Route(path: '/authentication/totp/cleartd/{_locale}/{allUsers}', name: 'app_totp_clear_trust_dev', requirements: ['_locale' => '%app.supported_locales%'])]
  public function clearTrustedDevice(UserRepository $userRep, bool $allUsers = false): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    if (!$allUsers) {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
      $user = $this->getUser();
      $user->clearTrustedToken();
      $this->addFlash('info', 'Your trusted devices were deleted.');
      $this->entityManager->flush();

      return $this->redirectToRoute('app_totp_manage');
    } else {
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      foreach ($userRep->findAll() as $user) {
        $user->clearTrustedToken();
      }
      $this->entityManager->flush();
      $this->addFlash('info', 'All trusted devices were deleted.');

      return $this->redirectToRoute('home');
    }
  }

  private function generateCode(int $digits = 6): int
  {
    $min = 10 ** ($digits - 1);
    $max = 10 ** $digits - 1;

    return random_int($min, $max);
  }

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

      return $bCodes;
    } else {
      return [];
    }
  }
}
