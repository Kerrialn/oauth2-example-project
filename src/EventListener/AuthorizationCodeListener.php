<?php

declare(strict_types=1);

namespace App\EventListener;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthorizationCodeListener
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function onAuthorizationRequest(AuthorizationRequestResolveEvent $event): void
    {
        $user = $this->security->getUser();

        if ($user instanceof UserInterface) {
            $event->setUser($user);
            $event->resolveAuthorization(true);
        } else {
            $response = new JsonResponse('authentication failed', 200);
            $event->setResponse($response);
        }
    }
}

