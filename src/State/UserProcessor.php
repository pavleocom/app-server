<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Webmozart\Assert\Assert;

class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,

        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $processor,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $data;

        if (!$user instanceof User) {
            throw new \Exception('Excepted User entity.');
        }

        Assert::string($user->plainPassword);

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, plainPassword: $user->plainPassword);

        $user->password = $hashedPassword;

        return $this->processor->process($user, $operation, $uriVariables, $context);
    }
}
