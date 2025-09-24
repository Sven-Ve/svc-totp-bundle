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
