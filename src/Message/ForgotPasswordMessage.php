<?php

declare(strict_types=1);

namespace App\Message;

class ForgotPasswordMessage
{
    public function __construct(
        public string $passwordTokenId,
    ) {
    }
}
