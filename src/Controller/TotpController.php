<?php

namespace Svc\TotpBundle\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TotpController extends AbstractController
{
  public function __construct(private readonly EntityManagerInterface $entityManager, private readonly string $homePath)
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

      return $this->render('@SvcTotp/totp/backCodesTotp.html.twig', [
        'backupcodes' => $this->generateBackCodes(),
      ]);
    }

    if (!$user->isTotpSecret()) {
      $user->setTotpSecret($totpAuthenticator->generateSecret());
      $this->entityManager->flush();
    }

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
    } else {
      $this->addFlash('warning', 'Cannot enable 2FA');
    }

    return $this->redirectToRoute('svc_totp_manage');
  }

  /**
   * disable 2fa but keep the secret for the current user.
   */
  public function disableTotp(): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $user = $this->getUser();
    if ($user->isTotpAuthenticationEnabled()) {
      $user->disableTotpAuthentication();
      $this->entityManager->flush();
    }

    return $this->redirectToRoute('svc_totp_manage');
  }

  /**
   * disable 2fa but keep the secret for another user.
   */
  public function disableOtherTotp(User $user): Response
  {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if ($user->isTotpAuthenticationEnabled()) {
      $user->disableTotpAuthentication();
      $this->entityManager->flush();
      $this->addFlash('info', '2FA for user ' . $user->getUserIdentifier() . ' disabled.');
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
      $this->addFlash('info', 'Your trusted devices were deleted.');
      $this->entityManager->flush();

      return $this->redirectToRoute('svc_totp_manage');
    } else {
      $this->denyAccessUnlessGranted('ROLE_ADMIN');

      /* @phpstan-ignore-next-line */
      foreach ($userRep->findBy(['isTotpAuthenticationEnabled' => true]) as $user) {
        $user->clearTrustedToken();
      }
      $this->entityManager->flush();
      $this->addFlash('info', 'All trusted devices were deleted.');

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

    $this->addFlash('info', 'The trusted devices for user ' . $user->getUserIdentifier() . ' were deleted.');

    return $this->redirectToRoute($this->homePath);
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
}
