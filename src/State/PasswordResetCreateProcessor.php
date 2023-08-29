<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PasswordResetCreateDto;
use App\Entity\PasswordReset;
use App\Entity\User;
use App\Message\PasswordResetMessage;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

class PasswordResetCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $processor,
        private MessageBusInterface $bus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $dto = $data;
        Assert::isInstanceOf($dto, PasswordResetCreateDto::class);

        $submittedEmail = $dto->email;

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $submittedEmail]);

        if (null !== $user) {

            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->eq('user', $user));
            $criteria->andWhere(Criteria::expr()->gte('expiresAt', new DateTimeImmutable('+30 minutes')));

            $existingPasswordReset = $this->em->getRepository(PasswordReset::class)->matching($criteria)->first();

            if (false === $existingPasswordReset) {
                $passwordReset = new PasswordReset();
                $passwordReset->user = $user;
                $passwordReset->expiresAt = new DateTimeImmutable('+1 day');
                $this->processor->process($passwordReset, $operation, $uriVariables, $context);
                $passwordResetId = $passwordReset->id;
            } else {
                $passwordResetId = $existingPasswordReset->id;
            }

            $this->bus->dispatch(new PasswordResetMessage($passwordResetId));
        }
    }
}