<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\PasswordResetCreateDto;
use App\Dto\PasswordResetUpdateDto;
use App\Generator\CryptoSecureIdGenerator;
use App\State\PasswordResetCreateProcessor;
use App\State\PasswordResetUpdateProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['created_at'], name: 'idx_password_reset_created_at')]
#[ORM\Index(columns: ['expires_at'], name: 'idx_password_reset_expires_at')]
#[ApiResource(
    operations: [
        new Post(
            status: 204,
            security: 'user == null',
            input: PasswordResetCreateDto::class,
            output: false,
            processor: PasswordResetCreateProcessor::class,
        ),
        new Post(
            uriTemplate: '/password-resets/{id}',
            status: 204,
            security: 'user == null',
            input: PasswordResetUpdateDto::class,
            output: false,
            processor: PasswordResetUpdateProcessor::class,
        ),
    ],
)]
class PasswordReset
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'string', unique: true)]
    #[ORM\CustomIdGenerator(class: CryptoSecureIdGenerator::class)]
    public string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public User $user;

    #[ORM\Column]
    public \DateTimeImmutable $expiresAt;

    #[ORM\Column]
    public \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
