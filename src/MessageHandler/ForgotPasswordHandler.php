<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\PasswordToken;
use App\Entity\User;
use App\Message\ForgotPasswordMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class ForgotPasswordHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ForgotPasswordMessage $message): void
    {
        $passwordTokenId = $message->passwordTokenId;

        /** @var PasswordToken|null $passwordToken */
        $passwordToken = $this->em->getRepository(PasswordToken::class)->find($passwordTokenId);

        $user = $passwordToken?->getUser();

        if ($user instanceof User && !$passwordToken->isExpired()) {
            // TODO: Set proper values. Should these values change per environment?

            $link = $passwordToken->getToken();

            $email = (new Email())
                ->from('noreply@blt.com')
                ->to($user->email)
                ->subject('Password recovery link')
                ->text("Please follow this link to reset your password: {$link}")
                ->html("<p>Please follow this link to reset your password: {$link}</p>");

            try {
                $this->mailer->send($email);
                $this->logger->info("[ForgotPassword] Password recovery email sent to user email: $user->email");
            } catch (\Throwable $e) {
                $this->logger->error("[ForgotPassword] Failed to send password recovery email to user email: $user->email");
                // TODO: capture exception
            }
        }
    }
}
