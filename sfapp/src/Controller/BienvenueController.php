<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BienvenueController extends AbstractController
{
    #[Route('/{role}/bienvenue', name: 'app_bienvenue')]
    public function index(Request $request, string $role): Response
    {
        $session = $request->getSession();
        $session->set('salleNom', 'C007');
        $utilisateur = $this->getUser();

        return $this->render('bienvenue/index.html.twig', [
            'role' => $role,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . " " . $utilisateur->getNom(),
        ]);
    }
}
