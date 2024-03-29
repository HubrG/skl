<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    #[Route(path: '/login-fdsfkjlksdjfljoklk', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
            // On renvoie sur la page précédente avec le referrer
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
    #[Route(path: '/login', name: 'app_login_full')]
    public function loginFull(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
            // On renvoie sur la page précédente avec le referrer
        }



        return $this->render('security/login-full.html.twig');
    }
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(Request $request): response
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        // On redirige vers la page précédente avec le referrer
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }
}
