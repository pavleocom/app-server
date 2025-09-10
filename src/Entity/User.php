<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Repository\UserRepository;
use App\State\UserProcessor;
use App\Validator\Constraint\UniqueEmail;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    operations: [
        new Get(security: 'user==object'),
        new Post(
            denormalizationContext: ['groups' => ['User:W$Registration']],
            validationContext: ['groups' => ['creation']],
            name: 'create_user',
            processor: UserProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['User:R$Default']],
)]
#[UniqueEntity(fields: 'email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        'User:R$Default',
    ])]
    public UuidInterface $id;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email]
    #[Assert\NotBlank]
    #[UniqueEmail(groups: ['creation'])]
    #[Groups([
        'User:R$Default',
        'User:W$Registration',
    ])]
    public string $email;

    /** @var string[] */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(min: 8)]
    #[Groups([
        'User:W$Registration',
    ])]
    public ?string $plainPassword = null;

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
