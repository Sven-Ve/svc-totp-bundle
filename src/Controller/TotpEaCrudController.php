<?php

namespace Svc\TotpBundle\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Svc\TotpBundle\Service\TotpLoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class TotpEaCrudController extends AbstractCrudController
{
  public function __construct(
    private readonly EntityManagerInterface $entityManager,
    private readonly AdminUrlGenerator $adminUrlGenerator)
  {
  }

  public static function getEntityFqcn(): string
  {
    return User::class;
  }

  /**
   * FIELDS.
   */
  public function configureFields(string $pageName): iterable
  {
    yield IdField::new('id')
      ->hideOnForm();
    yield EmailField::new('email');
    yield BooleanField::new('isTotpAuthenticationEnabled')
      ->renderAsSwitch(false)
      ->setLabel('MFA enabled');
    yield BooleanField::new('isTotpSecret')
      ->renderAsSwitch(false);
    yield IntegerField::new('getTrustedTokenVersion')
      ->setLabel('Trusted Token Version');
  }

  /**
   * FILTER.
   */
  public function configureFilters(Filters $filters): Filters
  {
    return parent::configureFilters($filters)
      ->add(BooleanFilter::new('isTotpAuthenticationEnabled')
        ->setLabel('MFA enabled')
      );
  }

  public function configureCrud(Crud $crud): Crud
  {
    return parent::configureCrud($crud)
      ->setPageTitle(Crud::PAGE_INDEX, 'MFA')
      ->showEntityActionsInlined()
      ->setHelp(Crud::PAGE_INDEX, 'Hier help');
  }

  /**
   * ACTIONS.
   */
  public function configureActions(Actions $actions): Actions
  {
    $disableMfaAction = Action::new('disableMFA')
      ->linkToCrudAction('disableMFA')
      ->setCssClass('btn btn-sm btn-danger')
      ->setLabel('Disable MFA')
      ->displayIf(static function (User $user) {
        return $user->isTotpAuthenticationEnabled();
      });
    $resetMfaAction = Action::new('resetMFA')
      ->linkToCrudAction('resetMFA')
      ->setLabel('Reset MFA')
      ->setCssClass('btn btn-sm btn-warning')
      ->displayIf(static function (User $user) {
        return $user->isTotpAuthenticationEnabled();
      });

    $deleteTdAction = Action::new('deleteTd')
      ->linkToCrudAction('deleteTd')
      ->setLabel('Clear Trusted Devices')
      ->setCssClass('btn btn-sm btn-info')
      ->displayIf(static function (User $user) {
        return $user->isTotpAuthenticationEnabled();
      });

    $clearAllTdAction = Action::new('deleteAllTd')
      ->linkToCrudAction('deleteAllTd')
      ->setLabel('Clear Trusted Devices (all)')
      ->setCssClass('btn btn-sm btn-primary')
    ->createAsGlobalAction();

    return parent::configureActions($actions)
      ->add(Crud::PAGE_INDEX, $disableMfaAction)
      ->add(Crud::PAGE_INDEX, $resetMfaAction)
      ->add(Crud::PAGE_INDEX, $deleteTdAction)
      ->add(Crud::PAGE_INDEX, $clearAllTdAction)
      ->disable(Action::NEW)
      ->disable(Action::DETAIL)
      ->disable(Action::EDIT)
      ->disable(Action::DELETE)
      ->reorder(Crud::PAGE_INDEX, ['disableMFA', 'resetMFA', 'deleteTd']);
  }

  public function disableMFA(AdminContext $adminContext): Response
  {
    return $this->handleDisableRestMFA(false, $adminContext);
  }

  public function resetMFA(AdminContext $adminContext): Response
  {
    return $this->handleDisableRestMFA(true, $adminContext);
  }

  public function handleDisableRestMFA(bool $reset, AdminContext $adminContext): Response
  {
    $user = $adminContext->getEntity()->getInstance();
    if (!$user instanceof User) {
      throw new \LogicException('Entity is missing or not a User');
    }
    $user->disableTotpAuthentication($reset);
    $this->entityManager->flush();

    $targetUrl = $this->adminUrlGenerator
      ->setController(self::class)
      ->setAction(Crud::PAGE_INDEX)
      ->generateUrl();

    return $this->redirect($targetUrl);
  }

  public function deleteTd(AdminContext $adminContext): Response
  {
    $user = $adminContext->getEntity()->getInstance();
    if (!$user instanceof User) {
      throw new \LogicException('Entity is missing or not a User');
    }
    $user->clearTrustedToken();
    $this->entityManager->flush();

    $this->addFlash('info', 'The trusted devices for user ' . $user->getUserIdentifier() . ' have been deleted. ');

    $targetUrl = $this->adminUrlGenerator
      ->setController(self::class)
      ->setAction(Crud::PAGE_INDEX)
      ->generateUrl();

    return $this->redirect($targetUrl);
  }

  public function deleteAllTd(UserRepository $userRep): Response
  {
    foreach ($userRep->findBy(['isTotpAuthenticationEnabled' => true]) as $user) {
      $user->clearTrustedToken();
//      $this->logger->log('TOTP trusted devices (all) cleared by ' . $this->getUser()->getUserIdentifier(), TotpLoggerInterface::LOG_TOTP_CLEAR_TD_BY_ADMIN, $user->getId());
    }
    $this->entityManager->flush();

    $this->addFlash('info', 'All trusted devices have been deleted. ');

    $targetUrl = $this->adminUrlGenerator
      ->setController(self::class)
      ->setAction(Crud::PAGE_INDEX)
      ->generateUrl();

    return $this->redirect($targetUrl);
  }
}
