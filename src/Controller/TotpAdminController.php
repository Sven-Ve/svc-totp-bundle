<?php

namespace Svc\TotpBundle\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TotpAdminController extends AbstractController
{
  public function index(UserRepository $userRep): Response
  {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    return $this->render('@SvcTotp/admin/users.html.twig', [
      'users' => $userRep->findAll(),
    ]);
  }
}
