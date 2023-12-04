<?php

namespace App\Services;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




class HubspotAPI extends AbstractController
{

    public function __construct(
        private UserRepository $uRepo
    ) {
    }

    public function addUser($user)
    {
        $tokenBearer = getenv('API_HUBSPOT_SECRET');
        // URL de l'API
        $url = 'https://api.hubapi.com/contacts/v1/contact';

        // Données à envoyer
        $data = array(
            "properties" => array(
                array(
                    "property" => "email",
                    "value" => $user->getEmail()
                ),
                array(
                    "property" => "firstname",
                    "value" =>  $user->getNickname()
                ),
            )
        );
        $dataJson = json_encode($data);

        // Initialiser cURL
        $ch = curl_init();

        // Définir les options de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $tokenBearer
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Exécuter la requête et récupérer la réponse
        $result = curl_exec($ch);

        // Fermer cURL
        curl_close($ch);
    }
}
