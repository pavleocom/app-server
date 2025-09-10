<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Validator\Constraint\UniqueEmail;
use App\Validator\UniqueEmailValidator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $processor,
        private ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $data;

        if (!$user instanceof User) {
            throw new \Exception('Excepted User entity.');
        }

        if ($operation->getName() === 'create_user') {
            // This step is not needed in Symfony/API Platform;
            // it will be validated automatically due to attribute on entity
            // This is only to demo how to validate manually
            // Groups allow us to determine when to validate (or not) field(s) against constraints/rules
            $violations = $this->validator->validate($user, groups: ['creation']);

            // do something with violations, e.g. serialise and return as response

            foreach ($violations as $violation) {
                // iterating
                $fieldName = $violation->getPropertyPath();
            }
        }



        Assert::string($user->plainPassword);

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, plainPassword: $user->plainPassword);

        $user->password = $hashedPassword;

        return $this->processor->process($user, $operation, $uriVariables, $context);
    }
}
