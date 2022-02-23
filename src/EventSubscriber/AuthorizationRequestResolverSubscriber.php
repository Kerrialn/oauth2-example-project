<?php

namespace App\EventSubscriber;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthorizationRequestResolverSubscriber implements EventSubscriberInterface
{
    public const SESSION_AUTHORIZATION_RESULT = '_app.oauth2.authorization_result';

    private RequestStack $requestStack;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => 'onAuthorizationRequestResolve',
        ];
    }

    public function onAuthorizationRequestResolve(AuthorizationRequestResolveEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request->getSession()->has(self::SESSION_AUTHORIZATION_RESULT)) {
            $event->resolveAuthorization(
                $request->getSession()->get(self::SESSION_AUTHORIZATION_RESULT)
            );
            $request->getSession()->remove(self::SESSION_AUTHORIZATION_RESULT);

        } else {
            $url = $this->urlGenerator->generate('app_consent', $request->query->all());

            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }

    }
}
