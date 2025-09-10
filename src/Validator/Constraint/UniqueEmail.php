<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class UniqueEmail extends Constraint
{
    public string $message = 'The email "{{ string }}" cannot be used.';
}