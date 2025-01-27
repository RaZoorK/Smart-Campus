<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Utilisateur;
use App\Model\Fonction;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle;

class ConnexionController extends AbstractController
{
    #[Route('/{role}/connexion', name: 'app_se_connecter')]
    public function index(AuthenticationUtils $authenticationUtils, string $role): Response
    {
        // Récupérer l'erreur de connexion, s'il y en a une
        $erreur = $authenticationUtils->getLastAuthenticationError();
        // Récupérer le dernier identifiant utilisé par l'utilisateur
        $nomUtilisateur = $authenticationUtils->getLastUsername();

        // Si une erreur existe, on peut personnaliser le message d'erreur
        if ($erreur) {
            $errorMessage = 'Nom d\'utilisateur ou mot de passe incorrect.';

            // Vous pouvez personnaliser plus d'erreurs ici
            if ($erreur->getMessageKey() === 'Bad credentials') {
                $errorMessage = 'Nom d\'utilisateur ou mot de passe incorrect.';
            } elseif ($erreur->getMessageKey() === 'User account is disabled') {
                $errorMessage = 'Votre compte a été désactivé.';
            }
            // Ajoutez d'autres conditions pour des erreurs spécifiques si nécessaire.
        } else {
            $errorMessage = null; // Pas d'erreur
        }

        // Rendre le template avec les données nécessaires
        return $this->render('connexion/fonction_co.html.twig', [
            'nomUtilisateur' => $nomUtilisateur,
            'erreur' => $errorMessage,
            'role' => $role,
        ]);
    }

    #[Route('/connexion', name: 'app_connexion')]
    public function redirectToConnexion(): Response
    {
        // Simple redirection vers la page de connexion
        return $this->render('connexion/index.html.twig');
    }

    #[Route('/redirect_donnees/{role}', name: 'app_redirect_to_bienvenue')]
    public function redirectToBienvenue(string $role): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $utilisateur = $this->getUser();

        // Vérifier que l'utilisateur est connecté et que la fonction est définie
        if ($utilisateur) {
            // Rediriger vers la route 'app_donnees' en passant le rôle comme paramètre
            return $this->redirectToRoute('app_bienvenue', ['role' => $role]);

        } else {
            // Rediriger vers la page de connexion si l'utilisateur n'est pas authentifié
            return $this->redirectToRoute('app_se_connecter', ['role' => $role]);
        }
    }
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Le contrôleur n'a pas besoin de code ici. Symfony gère la déconnexion automatiquement.
    }
}
