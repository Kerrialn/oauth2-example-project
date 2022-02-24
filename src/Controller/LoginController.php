<?php

declare(strict_types=1);

namespace App\Controller;

use App\EventSubscriber\AuthorizationRequestResolverSubscriber;
use App\Form\AuthorizationType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/consent', name: 'app_consent')]
    public function consent(Request $request): Response
    {
        $form = $this->createForm(AuthorizationType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            switch (true) {
                case
                    $form->get('allow')->isClicked():
                    $request->getSession()->set(
                        AuthorizationRequestResolverSubscriber::SESSION_AUTHORIZATION_RESULT,
                        true
                    );
                    break;
                case $form->get('deny')->isClicked():
                    $request->getSession()->set(
                        AuthorizationRequestResolverSubscriber::SESSION_AUTHORIZATION_RESULT,
                        false
                    );
                    break;
            }

            return $this->redirectToRoute('oauth2_authorize', $request->query->all());
        }

        return $this->render('login/authorization.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
