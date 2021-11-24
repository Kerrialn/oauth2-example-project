<?php

namespace App\EventListener;

use ECSPrefix20211002\Symfony\Component\HttpFoundation\RedirectResponse;
use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserResolveListener
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
            OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => 'resolve',
        ];
    }

    public function resolve(AuthorizationRequestResolveEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request->getSession()
            ->has(self::SESSION_AUTHORIZATION_RESULT)) {
            $event->resolveAuthorization(
                $request->getSession()
                    ->get(self::SESSION_AUTHORIZATION_RESULT)
            );
            $request->getSession()
                ->remove(self::SESSION_AUTHORIZATION_RESULT);

        } else {
            $response = new Response(
                $this->urlGenerator->generate(
                    'app_consent',
                    $request->query->all()
                ), 307
            );

            $event->setResponse($response);
        }

    }
}
