<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserParametersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserParameterController extends AbstractController
{

    private $em;

    private $userPRepo;

    private $userRepo;

    public function __construct(EntityManagerInterface $em, UserParametersRepository $userPRepo, UserRepository $userRepo)
    {
        $this->em = $em;
        $this->userPRepo = $userPRepo;
        $this->userRepo = $userRepo;
    }

    #[Route('/parameters/user/set', name: 'app_user_parameter')]
    public function index(Request $request): Response
    {
        $this->setParameters($request->get("param"), $request->get("value"));
        // On retourne en json
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'value' => $request->get("value"),
        ], 200);
    }
    private function setParameters($param, $value)
    {
        // On récupère l'utilisateur
        $user = $this->userRepo->find($this->getUser());
        if ($param == "darkmode") {
            $user->getUserParameters()->setDarkMode($value);
        }
        // On récupère la valeur de l'attribut
        // On sauvegarde
        $this->em->persist($user);
        $this->em->flush();
    }
}
