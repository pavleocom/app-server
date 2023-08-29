<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PasswordResetUpdateDto;
use App\Entity\PasswordReset;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Webmozart\Assert\Assert;

class PasswordResetUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var ?PasswordResetUpdateDto|object $dto */
        $dto = $data;
        Assert::isInstanceOf($dto, PasswordResetUpdateDto::class);

        Assert::keyExists($uriVariables, 'id');
        $id = $uriVariables['id'];

        /** @var ?PasswordReset $passwordReset */
        $passwordReset = $this->em->getRepository(PasswordReset::class)->find($id);

        if (null !== $passwordReset && !Carbon::createFromImmutable($passwordReset->expiresAt)->isPast()) {

            $user = $passwordReset->user;
            $hashedPassword = $this->userPasswordHasher->hashPassword($user, $dto->plainPassword);
            $user->password = $hashedPassword;

            $this->em->remove($passwordReset);

            $this->em->flush();
        } else {
            throw new NotFoundHttpException();
        }
    }
}