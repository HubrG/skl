<?php
# src/Security/GoogleAuthenticator.php
namespace App\Security;

use App\Entity\User;
use App\Entity\UserParameters;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;


class GoogleAuthenticator extends OAuth2Authenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;

    private $userAuthenticator;

    private $authenticator;


    public function __construct(AppAuthenticator $authenticator, UserAuthenticatorInterface $userAuthenticator, ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->userAuthenticator = $userAuthenticator;
        $this->authenticator = $authenticator;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);
        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();

                // have they logged in with Google before? Easy!
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);

                //User doesnt exist, we create it !
                if (!$existingUser) {
                    $existingUser = new User();
                    $existingUser->setEmail($email);
                    $existingUser->setProfilPicture($googleUser->getAvatar());
                    $existingUser->setUsername(ucfirst($googleUser->getFirstName()) . ucfirst(substr($googleUser->getName(), 0, 1)) . rand(1, 999));
                    $existingUser->setIsVerified(1);
                    $existingUser->setGoogleId($googleUser->getId());
                    $existingUser->setPassword("");
                    $this->entityManager->persist($existingUser);
                    //
                    $userParameters = new UserParameters();
                    // On crée une table UserParameters pour chaque utilisateur
                    $userParameters->setUser($existingUser);
                    $this->entityManager->persist($userParameters);
                    $this->entityManager->flush();
                }
                $this->entityManager->flush();
                return $existingUser;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_dashboard" to some route in your app

        // $request->getSession()->set('_security_' . $firewallName, serialize($token));
        $request->getSession()->set('_security_' . $firewallName, serialize($token));

        return new RedirectResponse(
            $this->router->generate('app_home')
        );

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    //    public function start(Request $request, AuthenticationException $authException = null): Response
    //    {
    //        
    // * If you would like this class to control what happens when an anonymous user accesses a
    // * protected page (e.g. redirect to /login), uncomment this method and make this class
    // * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    // *
    // * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point

    //    }
}
