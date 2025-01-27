<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Entity\Utilisateur;
use App\Form\ModifTechType;
use App\Form\UtilisateurFiltreType;
use App\Form\AjoutTechType;
use App\Repository\NotificationRepository;
use App\Repository\SalleRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GestionTechController extends AbstractController
{
    #[Route('/{role}/gestion-tech', name: 'app_gestion_tech')]
    public function index(EntityManagerInterface $em, Request $request, string $role, SalleRepository $salleRepository, UtilisateurRepository $utilisateurRepository, UserPasswordHasherInterface $passwordHasher): Response
    {

        $utilisateur = $this->getUser();
        // Créez le formulaire de recherche
        $form = $this->createForm(UtilisateurFiltreType::class);
        $form->handleRequest($request);

        // Récupérez les données en fonction du filtre
        $queryBuilder = $em->getRepository(Utilisateur::class)->createQueryBuilder('tech');

        $queryBuilder->andWhere('tech.fonction = :fonction')
            ->setParameter('fonction', 'Technicien');

        // Ajouter le tri par nom en ordre croissant (ASC)
        $queryBuilder->orderBy('tech.nom', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $donnees = $form->getData();

            // Si 'nom' est présent dans l'objet Utilisateur, on applique le filtre
            if (!empty($donnees->getNom())) {
                $nom = $donnees->getNom();

                // Assurez-vous que vous passez le bon type à la requête
                // Si 'nom' est une chaîne de caractères, vous n'avez rien à faire ici
                $queryBuilder->andWhere('tech.nom LIKE :nom')
                    ->setParameter('nom', '%' . $nom . '%');
            }
        }

        // Exécutez la requête pour récupérer les résultats
        $techniciens = $queryBuilder->getQuery()->getResult();

        // Récupérer toutes les salles
        $salles = $salleRepository->findAll();
        $salleNom = $request->getSession()->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée
        return $this->render('gestion_tech/index.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
            'techniciens' => $techniciens,
            'salles' => $salles,
            'salleNom' => $salleNom,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }

    #[Route("{role}/gestion-tech/ajouter", name: "app_gestion_tech_ajouter")]
    public function ajouterTech(Request $request, string $role, SalleRepository $salleRepository, UtilisateurRepository $utilisateurRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $session = $request->getSession();
        $utilisateur = $this->getUser();
        $technicien = new Utilisateur();

        $technicien->setFonction("Technicien");
        $roles[] = 'ROLE_TECHNICIEN';
        $technicien->setRoles($roles);

        $form = $this->createForm(ajoutTechType::class, $technicien, [
            "method" => "POST",
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($technicien, $technicien->getPassword());

            $technicien->setPassword($hashedPassword);
            $utilisateurRepository->ajoutTech($technicien, true);

            return $this->redirectToRoute('app_gestion_tech', ['role' => $role]);
        }

        // Récupérer toutes les salles
        $salles = $salleRepository->findAll();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        return $this->render('gestion_tech/ajouter_tech.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
            'salles' => $salles,
            'salleNom' => $salleNom,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }

    #[Route("{role}/gestion-tech/modifier/{id}", name: "app_gestion_tech_modifier")]
    public function modifierTech(Request $request, string $role, int $id, SalleRepository $salleRepository, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UtilisateurRepository $techRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $session = $request->getSession();
        $utilisateur = $this->getUser();

        // Trouver l'utilisateur (technicien) à modifier
        $technicien = $utilisateurRepository->find($id);

        if (!$technicien) {
            $this->addFlash('error', 'Technicien non trouvé');
            return $this->redirectToRoute('app_gestion_tech', ['role' => $role]);
        }

        // Créer le formulaire pour modifier le technicien
        $form = $this->createForm(ModifTechType::class, $technicien, [
            "method" => "POST",
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification si un mot de passe a été fourni
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                // Si un mot de passe est fourni, on le hache
                $hashedPassword = $passwordHasher->hashPassword($technicien, $plainPassword);
                $technicien->setPassword($hashedPassword);
            }

            // Si aucun mot de passe n'est fourni, on garde l'ancien mot de passe

            // Sauvegarder les changements
            $entityManager->persist($technicien);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Technicien modifié avec succès.');

            // Redirection vers la liste des techniciens
            return $this->redirectToRoute('app_gestion_tech', ['role' => $role]);
        }

        // Récupérer toutes les salles
        $salles = $salleRepository->findAll();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée


        return $this->render('gestion_tech/modifier_tech.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
            'technicien' => $technicien,
            'salles' => $salles,
            'salleNom' => $salleNom,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }

    #[Route("{role}/gestion-tech/supprimer-confirm/{id}", name: "app_gestion_tech_supprimer")]
    public function supprimerTech(Request $request, int $id, string $role, UtilisateurRepository $utilisateurRepository, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée


        // Récupérer le technicien à supprimer
        $technicien = $utilisateurRepository->find($id);

        if (!$technicien) {
            $this->addFlash('error', 'Technicien non trouvé');
            return $this->redirectToRoute('app_gestion_tech', ['role' => $role]);
        }

        if ($notificationRepository->countNotificationsNonLues($utilisateur, $technicien) == 0)
        {
            $nom = $technicien->getNom();
            $prenom = $technicien->getPrenom();
            foreach ($technicien->getNotificationsEnvoyees() as $notification) {
                $notification->setExpediteurNom($nom);
                $notification->setExpediteurPrenom($prenom);
                $technicien->removeNotificationsEnvoyee($notification);
            }
            // Supprimer le technicien
            $entityManager->remove($technicien);
            $entityManager->flush();
        }
        else{
            $this->addFlash('error', 'Impossible de supprimer le technicien, vous avez des notifications non lues de sa part' );
            return $this->redirectToRoute('app_gestion_tech_supprimer_confirm', ['role' => $role, 'id' => $id]);
        }


        // Message de succès
        $this->addFlash('success', 'Technicien supprimé avec succès.');

        return $this->redirectToRoute('app_gestion_tech', ['role' => $role]);
    }

    #[Route("{role}/gestion-tech/supprimer/{id}", name: "app_gestion_tech_supprimer_confirm")]
    public function supprimerConfirmTech(Request $request, string $role, SalleRepository $salleRepository, int $id, UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();

        // Récupérer toutes 5les salles
        $salles = $salleRepository->findAll();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        $technicien = $utilisateurRepository->find($id);

        if (!$technicien) {
            $this->addFlash('error', 'Technicien non trouvé');
            return $this->redirectToRoute('app_gestion_tech', ['role' => $role]);
        }

        return $this->render('gestion_tech/supprimer_tech.html.twig', [
            'technicien' => $technicien,
            'role' => $role,
            'salles' => $salles,
            'salleNom' => $salleNom,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }
}
