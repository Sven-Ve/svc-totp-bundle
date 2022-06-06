<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping as ORM;
use Svc\TotpBundle\Service\_TotpTrait;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User

{
  use _TotpTrait;

  private $id;
  private $email;

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function getUserIdentifier(): ?string {
    return $this->email;
  }
}