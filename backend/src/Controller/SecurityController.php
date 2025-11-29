<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    // Ez a végpont csak arra szolgál, hogy a Symfony Router felismerje
    // az útvonalat. A logikát (hitelesítést és a JWT generálását)
    // a security.yaml-ban beállított 'json_login' mechanizmus kezeli.
    #[Route('/api/login_check', name: 'app_login_check', methods: ['POST'])]
    public function loginCheck()
    {
        // A kérés ide soha nem jut el! A Lexik Bundle elfogja.
        throw new \Exception('This code should not be reached!');
    }
}
