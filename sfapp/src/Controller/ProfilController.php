<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ModifProfilType;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProfilController extends AbstractController
{
    #[Route('{role}/profil', name: 'app_profil')]
    public function index(string $role, Request $request, SessionInterface $session): Response
    {
        // Récupérer l'URL précédente
        $previousUrl = $request->headers->get('referer');

        // Si la page précédente n'est pas "profil/edite", enregistrer l'URL
        if (strpos($previousUrl, '/profil/edite') === false) {
            $session->set('previous_url', $previousUrl);
        }

        $utilisateur = null;

        // Vérification que l'utilisateur est connecté
        if (!$user = $this->getUser()) {
            return $this->redirectToRoute('app_login'); // Redirection si non connecté
        }

        if ($role !== 'Occupant') {
            // Récupération de l'utilisateur connecté
            $utilisateur = $this->getUser();

            // Vérification des droits d'accès
            if ($utilisateur->getFonction() === 'Technicien' && $role === 'Manager') {
                $this->addFlash(
                    'error',
                    'Accès interdit : les techniciens ne peuvent pas accéder à la page des managers.'
                );
                return $this->redirectToRoute('app_se_connecter', ['role' => 'Technicien']);
            }
        }

        return $this->render('profil/index.html.twig', [
            'role' => $role,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur ? $utilisateur->getPrenom() . " " . $utilisateur->getNom() : null,
        ]);
    }

    #[Route('{role}/profil/edite', name: 'app_profil_edite')]
    public function profilEdite(Request $request, string $role, UserPasswordHasherInterface $passwordHasher, UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateur = $this->getUser();

        // Vérification des droits d'accès
        if ($role !== 'Occupant' && $utilisateur->getFonction() === 'Technicien' && $role === 'Manager') {
            $this->addFlash(
                'error',
                'Accès interdit : les techniciens ne peuvent pas accéder à la page des managers.'
            );
            return $this->redirectToRoute('app_se_connecter', ['role' => 'Technicien']);
        }

        // Créer le formulaire pour éditer le profil
        $form = $this->createForm(ModifProfilType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de l'unicité de l'email
            $newEmail = $form->get('mail')->getData();
            $existingUserByEmail = $utilisateurRepository->findOneBy(['mail' => $newEmail]);

            if ($existingUserByEmail && $existingUserByEmail->getId() !== $utilisateur->getId()) {
                $this->addFlash('error_mail', 'Cet e-mail est déjà utilisé par un autre compte.');
                return $this->redirectToRoute('app_profil_edite', ['role' => $role]);
            }

            // Vérification de l'unicité du numéro de téléphone
            $newPhone = $form->get('telephone')->getData();
            $existingUserByPhone = $utilisateurRepository->findOneBy(['telephone' => $newPhone]);

            if ($existingUserByPhone && $existingUserByPhone->getId() !== $utilisateur->getId()) {
                $this->addFlash('error_phone', 'Ce numéro de téléphone est déjà utilisé par un autre compte.');
                return $this->redirectToRoute('app_profil_edite', ['role' => $role]);
            }

            // Vérification si un mot de passe a été fourni
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                // Si un mot de passe est fourni, on le hache
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($hashedPassword);
            }

            // Sauvegarde des modifications dans la base de données
            $utilisateurRepository->save($utilisateur);

            // Message flash pour succès
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            // Redirection vers la page de profil après modification
            return $this->redirectToRoute('app_profil', ['role' => $role]);
        }

        return $this->render('profil/profil_edite.html.twig', [
            'role' => $role,
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
            'nomUtilisateur' => $utilisateur ? $utilisateur->getPrenom() . " " . $utilisateur->getNom() : null,
        ]);
    }

}
