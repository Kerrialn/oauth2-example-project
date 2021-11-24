
# Symfony Integration
## Authorization Code Grant


1. Install package: `composer require league/oauth2-server-bundle`
2. configure `./config/packages/league_oauth2_server.yaml` :

```
league_oauth2_server:
    authorization_server:
        private_key: '%kernel.project_dir%/var/oauth/private.key'
        private_key_passphrase: '%env(string:OAUTH2_ENCRYPTION_KEY)%'
        encryption_key: '%env(string:OAUTH2_ENCRYPTION_KEY)%'
        access_token_ttl:     PT1H
        refresh_token_ttl:    P1M
        auth_code_ttl:        PT10M
        enable_client_credentials_grant: true
        enable_password_grant: true
        enable_refresh_token_grant: true
        enable_auth_code_grant: true
        require_code_challenge_for_public_clients: true
        enable_implicit_grant: true
    resource_server:
        public_key: '%kernel.project_dir%/var/oauth/public.key'
    scopes:
            available:
                - default
            default:
                - default
    persistence:
        doctrine: null
```

3. Add OAUTH2_ENCRYPTION_KEY in `.env` use `php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'` to generate key
4. Create oauth directory: `mkdir ./var/oauth`
5. Generate private key: `openssl genrsa -passout pass:<REPLACE_WITH_OAUTH2_ENCRYPTION_KEY> -out ./var/oauth/private.key 4096`
6. Generate public key: `openssl rsa -in ./var/oauth/private.key -passin pass:<REPLACE_WITH_OAUTH2_ENCRYPTION_KEY> -pubout -out ./var/oauth/public.key`
7. Create `src/EventListener/AuthorizationCodeListener.php`:

```
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
```

8. Register AuthorizationCodeListener as service: 

```
    App\EventListener\AuthorizationCodeListener:
        tags:
            - { name: kernel.event_listener, event: 'league.oauth2_server.event.authorization_request_resolve', method: onAuthorizationRequest }
```

9. create client: `bin/console league:oauth2-server:create-client api-client --grant-type "authorization_code"`
10. 
