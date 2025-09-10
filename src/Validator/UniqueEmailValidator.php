<?php

namespace App\Validator;

use App\Entity\User;
use App\Validator\Constraint\UniqueEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This is an example validator
 */
class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueEmail::class);
        }

        if (null === $value || '' === $value) {
            return; // use NotNull/NotEmpty constraints to prevent null or blank values
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $value]);

        if ($user instanceof User) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}