<?php

declare(strict_types=1);

namespace App\Message;

class PasswordResetMessage
{
    public function __construct(
        public string $passwordResetId,
    ) {
    }
}
