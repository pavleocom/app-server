<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\PasswordReset;
use App\Entity\User;
use App\Message\PasswordResetMessage;
use Carbon\Carbon;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class PasswordResetHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(PasswordResetMessage $message): void
    {
        $passwordResetId = $message->passwordResetId;

        $passwordReset = $this->em->getRepository(PasswordReset::class)->find($passwordResetId);

        if (!$passwordReset instanceof PasswordReset) {
            $this->logger->info("[PasswordReset] Could not find password reset with id {$passwordResetId}");
            return;
        }

        $user = $passwordReset->user;
        $isExpired = Carbon::createFromImmutable($passwordReset->expiresAt)->isPast();

        if ($isExpired)  {
            $this->logger->info("[PasswordReset] Not sending password reset email to user id {$user->id} because password reset is expired");
            return;
        }

        // TODO: Set proper values. Should these values change per environment?

        $link = $passwordResetId;

        $email = (new Email())
            ->from('noreply@app-server.com')
            ->to($user->email)
            ->subject('Password recovery link')
            ->text("Please follow this link to reset your password: {$link}")
            ->html("<p>Please follow this link to reset your password: {$link}</p>");

        try {
            $this->mailer->send($email);
            $this->logger->info("[PasswordReset] Password recovery email sent to user email: $user->email");
        } catch (\Throwable $e) {
            $this->logger->error("[PasswordReset] Failed to send password recovery email to user email: $user->email");
            // TODO: capture exception
        }
    }
}
