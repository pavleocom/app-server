<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Message\PasswordResetMessage;
use CoopTilleuls\ForgotPasswordBundle\Event\CreateTokenEvent;
use CoopTilleuls\ForgotPasswordBundle\Event\UpdatePasswordEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Webmozart\Assert\Assert;

final readonly class ForgotPasswordListener
{
    public function __construct(
        private LoggerInterface $logger,
        private MessageBusInterface $bus,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $userPasswordHasher,
        private Security $security,
    ) {
    }

    #[AsEventListener(event: CreateTokenEvent::class)]
    public function onCreateToken(CreateTokenEvent $event): void
    {
        $token = $event->getPasswordToken();
        $user = $token->getUser();

        $this->logger->info("[ForgotPassword] Token generated for user email: {$user?->email}");

        $this->bus->dispatch(new PasswordResetMessage(passwordTokenId: (string) $token->getId()));
    }

    #[AsEventListener(event: UpdatePasswordEvent::class)]
    public function onUpdatePassword(UpdatePasswordEvent $event): void
    {
        $token = $event->getPasswordToken();
        $user = $token->getUser();
        $newPassword = $event->getPassword(); // TODO: check password strength, what if too weak?
        Assert::string($newPassword);

        if ($user instanceof User && !$token->isExpired()) {
            $hashedPassword = $this->userPasswordHasher->hashPassword($user, $newPassword);
            $user->password = $hashedPassword;
            $this->em->flush();
        }
    }

    #[AsEventListener(event: RequestEvent::class, priority: -1)]
    public function onKernelRequest(RequestEvent $event): void
    {
        // TODO: I don't like this validation, is there a better way to do this?
        $route = $event->getRequest()->get('_route');

        if (!$event->isMainRequest() || !str_starts_with($route, 'coop_tilleuls_forgot_password')) {
            return;
        }

        $user = $this->security->getUser();

        if (null !== $user) {
            throw new AccessDeniedException(); // don't allow authenticated users access password recovery routes
        }

        if ('coop_tilleuls_forgot_password.update' === $route) {
            // new password validation
            $request = $event->getRequest();
            $body = $request->getContent();

            $array = \json_decode($body, true);
            Assert::isArray($array);
            $newPassword = $array['password'];

            if (mb_strlen($newPassword) < 8) {
                $response = new JsonResponse([
                    '@context' => '/contexts/ConstraintViolationList',
                    '@type' => 'ConstraintViolationList',
                    'violations' => [
                        0 => [
                            'propertyPath' => 'password',
                            'message' => 'This value must be at least 8 characters long.',
                        ],
                    ]], 422, ['Content-Type' => 'application/ld+json; charset=utf-8']);

                $event->setResponse($response);
            }
        }
    }
}
