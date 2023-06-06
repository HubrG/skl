<?php

namespace App\Controller;

use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserParametersRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserParameterController extends AbstractController
{

    private $em;

    private $userPRepo;

    private $userRepo;
    private EmailVerifier $emailVerifier;

    public function __construct(EntityManagerInterface $em, UserParametersRepository $userPRepo, UserRepository $userRepo, EmailVerifier $emailVerifier)
    {
        $this->em = $em;
        $this->userPRepo = $userPRepo;
        $this->userRepo = $userRepo;
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/param/user/set', name: 'app_user_parameter', methods: ['POST'])]
    public function index(Request $request, SessionInterface $session): Response
    {
        // * SESSIONS
        if (!$this->getUser()) {
            // DARKMODE SESSION
            if ($request->get("param") == "darkmode" && $request->get("value") == 1) {
                $session->set('darkmode', true);
                $message = "Darkmode activé";
            } elseif ($request->get("param") == "darkmode" && $request->get("value") == 0) {
                $session->remove('darkmode');
                $message = "Darkmode désactivé";
            } elseif ($request->get("param") == "grid" && $request->get("value") == 1) {
                $session->set('grid', true);
                $message = "Petite grille activée";
            } elseif ($request->get("param") == "grid" && $request->get("value") == 0) {
                $session->remove('grid');
                $message = "Grande grille activée";
            }
            return $this->json([
                'message' => $message,
                'value' => $request->get("value"),
            ], 200);
        }
        // * LOGGED
        else {
            $this->setParameters($request->get("param"), $request->get("value"));
            // On retourne en json
            return $this->json([
                'message' => 'Paramètre modifié',
                'value' => $request->get("value"),
            ], 200);
        }
    }
    private function setParameters($param, $value)
    {
        // On récupère l'utilisateur
        $user = $this->userRepo->find($this->getUser());
        if ($param == "darkmode") {
            $user->getUserParameters()->setDarkMode($value);
        }
        if ($param == "grid") {
            $user->getUserParameters()->setGridShow($value);
        }
        // On récupère la valeur de l'attribut
        // On sauvegarde
        $this->em->persist($user);
        $this->em->flush();
    }
    #[Route('/param/user/set/notification', name: 'app_user_parameter_notification', methods: ['POST'])]
    public function setNotification(Request $request, SessionInterface $session): Response
    {
        $type = $request->get("type");
        $nb = $request->get("nb");
        $value = $request->get("value");
        if ($value == "true") {
            $value = 1;
        } else {
            $value = 0;
        }
        //
        $user = $this->userRepo->find($this->getUser());
        if ($type == "mail") {
            if ($nb == 1) {
                $user->getUserParameters()->setNotif1Mail($value);
            } elseif ($nb == 2) {
                $user->getUserParameters()->setNotif2Mail($value);
            } elseif ($nb == 3) {
                $user->getUserParameters()->setNotif3Mail($value);
            } elseif ($nb == 4) {
                $user->getUserParameters()->setNotif4Mail($value);
            } elseif ($nb == 5) {
                $user->getUserParameters()->setNotif5Mail($value);
            } elseif ($nb == 6) {
                $user->getUserParameters()->setNotif6Mail($value);
            } elseif ($nb == 7) {
                $user->getUserParameters()->setNotif7Mail($value);
            } elseif ($nb == 8) {
                $user->getUserParameters()->setNotif8Mail($value);
            } elseif ($nb == 9) {
                $user->getUserParameters()->setNotif9Mail($value);
            } elseif ($nb == 10) {
                $user->getUserParameters()->setNotif10Mail($value);
            } elseif ($nb == 11) {
                $user->getUserParameters()->setNotif11Mail($value);
            } elseif ($nb == 12) {
                $user->getUserParameters()->setNotif12Mail($value);
            } elseif ($nb == 13) {
                $user->getUserParameters()->setNotif13Mail($value);
            } elseif ($nb == 14) {
                $user->getUserParameters()->setNotif14Mail($value);
            } elseif ($nb == 15) {
                $user->getUserParameters()->setNotif15Mail($value);
            } elseif ($nb == 16) {
                $user->getUserParameters()->setNotif16Mail($value);
            } elseif ($nb == 17) {
                $user->getUserParameters()->setNotif17Mail($value);
            } elseif ($nb == 18) {
                $user->getUserParameters()->setNotif18Mail($value);
            } elseif ($nb == 19) {
                $user->getUserParameters()->setNotif19Mail($value);
            }
        } else {
            if ($nb == 1) {
                $user->getUserParameters()->setNotif1Web($value);
            } elseif ($nb == 2) {
                $user->getUserParameters()->setNotif2Web($value);
            } elseif ($nb == 3) {
                $user->getUserParameters()->setNotif3Web($value);
            } elseif ($nb == 4) {
                $user->getUserParameters()->setNotif4Web($value);
            } elseif ($nb == 5) {
                $user->getUserParameters()->setNotif5Web($value);
            } elseif ($nb == 6) {
                $user->getUserParameters()->setNotif6Web($value);
            } elseif ($nb == 7) {
                $user->getUserParameters()->setNotif7Web($value);
            } elseif ($nb == 8) {
                $user->getUserParameters()->setNotif8Web($value);
            } elseif ($nb == 9) {
                $user->getUserParameters()->setNotif9Web($value);
            } elseif ($nb == 10) {
                $user->getUserParameters()->setNotif10Web($value);
            } elseif ($nb == 11) {
                $user->getUserParameters()->setNotif11Web($value);
            } elseif ($nb == 12) {
                $user->getUserParameters()->setNotif12Web($value);
            } elseif ($nb == 13) {
                $user->getUserParameters()->setNotif13Web($value);
            } elseif ($nb == 14) {
                $user->getUserParameters()->setNotif14Web($value);
            } elseif ($nb == 15) {
                $user->getUserParameters()->setNotif15Web($value);
            } elseif ($nb == 16) {
                $user->getUserParameters()->setNotif16Web($value);
            } elseif ($nb == 17) {
                $user->getUserParameters()->setNotif17Web($value);
            } elseif ($nb == 18) {
                $user->getUserParameters()->setNotif18Web($value);
            } elseif ($nb == 19) {
                $user->getUserParameters()->setNotif19Web($value);
            }
        }
        // On récupère la valeur de l'attribut
        // On sauvegarde
        $this->em->persist($user);
        $this->em->flush();
        //
        return $this->json([
            'message' => 'Paramètre modifié',
            'value' => $request->get("value"),
            'type' => $request->get("type"),
            'nb' => $request->get("nb"),
        ], 200);
    }
    #[Route('/param/user/resend_validation_email', name: 'app_user_resend_mail_validation', methods: ['POST'])]
    public function resendMail(Request $request, SessionInterface $session): Response
    {
        $user = $this->userRepo->find($this->getUser());
        // $user->setIsVerified(false);
        // $this->em->persist($user);
        // $this->em->flush();
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('admin@scrilab.com', 'Scrilab'))
                ->to($user->getEmail())
                ->subject('Confirmez votre adresse adresse email.')
                ->htmlTemplate('emails/valid_email.html.twig')
        );
        return $this->json([
            'message' => 'Mail envoyé',
        ], 200);
    }
}
