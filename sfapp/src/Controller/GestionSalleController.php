<?php

namespace App\Controller;

use App\Entity\SA;
use App\Entity\Salle;
use App\Form\AjoutSalleType;
use App\Form\ModifSalleType;
use App\Form\SalleFilterType;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class GestionSalleController extends AbstractController
{
    #[Route('{role}/gestion-salles', name: 'app_gestion_salles')]
    public function index(EntityManagerInterface $em, string $role, SalleRepository $salleRepository, Request $request): Response
    {
        $utilisateur = $this->getUser();
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut

        // Récupérer les bâtiments et étages existants
        $batiments = $em->createQueryBuilder()
            ->select('DISTINCT s.batiment')
            ->from(Salle::class, 's')
            ->orderBy('s.batiment', 'ASC')
            ->getQuery()
            ->getResult();

        $etages = $em->createQueryBuilder()
            ->select('DISTINCT s.etage')
            ->from(Salle::class, 's')
            ->orderBy('s.etage', 'ASC')
            ->getQuery()
            ->getResult();

        // Transforme les résultats pour les passer au formulaire
        $choix_batiments = array_column($batiments, 'batiment');
        $choix_etages = array_column($etages, 'etage');

        // Créez le formulaire de recherche
        $form = $this->createForm(SalleFilterType::class, null, [
            'choix_batiments' => array_combine($choix_batiments, $choix_batiments),
            'choix_etages' => array_combine($choix_etages, $choix_etages),
        ]);

        $form->handleRequest($request);

        $queryBuilder = $em->getRepository(Salle::class)->createQueryBuilder('salle');
        $queryBuilder->orderBy('salle.Nom', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!empty($data['nom'])) {
                $queryBuilder->andWhere('salle.Nom LIKE :nom')
                    ->setParameter('nom', '%' . $data['nom'] . '%');
            }
            if (!empty($data['batiment'])) {
                $queryBuilder->andWhere('salle.batiment = :batiment')
                    ->setParameter('batiment', $data['batiment']);
            }
            if (!empty($data['etage'])) {
                $queryBuilder->andWhere('salle.etage = :etage')
                    ->setParameter('etage', $data['etage']);
            }
        }

        $salles = $queryBuilder->getQuery()->getResult();

        return $this->render('gestion_salle/index.html.twig', [
            'controller_name' => 'GestionSalleController',
            'salles' => $salles,
            'role' => $role,
            'form' => $form->createView(),
            'salleNom' => $salleNom,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }

    #[Route("{role}/gestion-salles/ajouter", name: "app_gestion_salle_ajouter")]
    public function ajouterSalle(Request $request, string $role, SalleRepository $salleRepository, EntityManagerInterface $em, SARepository $SARepository): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        // Récupérer toutes les salles existantes
        $queryBuilder = $em->getRepository(Salle::class)->createQueryBuilder('salle');
        $salles = $queryBuilder->getQuery()->getResult(); // Récupère toutes les salles

        // Récupérer tous les bâtiments existants depuis la base de données
        $batiments = $em->getRepository(Salle::class)->createQueryBuilder('s')
            ->select('DISTINCT s.batiment') // Sélectionne les bâtiments distincts
            ->getQuery()
            ->getResult();

        // Transformer la liste des bâtiments en tableau de choix
        $choix_batiments = [];
        foreach ($batiments as $batiment) {
            $choix_batiments[$batiment['batiment']] = $batiment['batiment'];
        }

        // Ajouter "Autre" en option
        $choix_batiments['Autre'] = 'Autre';

        // Création d'une nouvelle salle
        $salle = new Salle();
        $form = $this->createForm(AjoutSalleType::class, $salle, [
            'choix_batiments' => $choix_batiments, // Passer les choix des bâtiments
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Vérifiez si "Autre" a été sélectionné
            $nouveauBatiment = $form->get('nouveauBatiment')->getData();
            $batiment = $form->get('batiment')->getData();

            if ($batiment === 'Autre' && !empty($nouveauBatiment)) {
                $salle->setBatiment($nouveauBatiment); // Utilisez le nouveau bâtiment
            } else {
                $salle->setBatiment($batiment); // Utilisez le bâtiment sélectionné
            }

            // Vérification d'existence
            $nomSalle = $salle->getNom();
            $batimentSalle = $salle->getBatiment();
            if ($salleRepository->salleExisteDansBatiment($nomSalle, $batimentSalle)) {
                $this->addFlash('error', "Une salle nommée '{$nomSalle}' existe déjà dans le bâtiment '{$batimentSalle}'.");

                return $this->redirectToRoute('app_gestion_salle_ajouter', ['role' => $role]);
            }

            // Enregistrez la salle
            $salleRepository->ajoutSalle($salle, true);

            $this->addFlash('success', 'Salle ajoutée avec succès.');
            return $this->redirectToRoute('app_gestion_salles', ['role' => $role]);
        }
        return $this->render('gestion_salle/ajouter_salle.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
            'salleNom' => $salleNom,
            'salles' => $salles, // Passer la liste des salles obtenues
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }


    #[Route("{role}/gestion-salles/modifier/{Nom}", name: "app_gestion_salle_modifier")]
    public function modifierSalle(Request $request, string $role, string $Nom, SalleRepository $salleRepository, EntityManagerInterface $em, SARepository $SARepository): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007');

        $salle = $salleRepository->findOneBy(['Nom' => $Nom]);

        if (!$salle) {
            throw $this->createNotFoundException("Salle introuvable.");
        }

        $ancienSA = $salle->getSA();

        // Récupérer les bâtiments existants
        $batiments = $em->getRepository(Salle::class)->createQueryBuilder('s')
            ->select('DISTINCT s.batiment')
            ->getQuery()
            ->getResult();

        // Transformer en tableau de choix
        $choix_batiments = [];
        foreach ($batiments as $batiment) {
            $choix_batiments[$batiment['batiment']] = $batiment['batiment'];
        }

        $form = $this->createForm(ModifSalleType::class, $salle, [
            'method' => 'POST',
            'salle_actuelle' => $salle,
            'choix_batiments' => $choix_batiments,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de l'unicité du nom
            $sallesExistantes = $salleRepository->findOneBy(['Nom' => $salle->getNom()]);
            if ($sallesExistantes && $sallesExistantes->getId() !== $salle->getId()) {
                $this->addFlash('error', 'Une salle avec ce nom existe déjà.');
                return $this->redirectToRoute('app_gestion_salle_modifier', [
                    'role' => $role,
                    'Nom' => $salle->getNom(),
                    'salleNom' => $salleNom,
                ]);
            }

            // Traitement du bâtiment
            $batiment = $form->get('Batiment')->getData();
            $salle->setBatiment($batiment);

            // Gestion du SA
            $sa = $salle->getSA();
            if ($sa) {
                $saExistant = $SARepository->find($sa->getId());
                if ($ancienSA != null) {
                    $ancienSA->setSalle(null);
                }

                if ($saExistant) {
                    $salle->setSA($saExistant);
                } else {
                    $SARepository->save($sa, true);
                }
            }

            $salleRepository->modifier_Salle($salle, true);

            $this->addFlash('success', 'Salle modifiée avec succès.');
            return $this->redirectToRoute('app_gestion_salles', ['role' => $role]);
        }

        $queryBuilder = $em->getRepository(Salle::class)->createQueryBuilder('salle');
        // Exécutez la requête pour récupérer les salles
        $salles = $queryBuilder->getQuery()->getResult();

        return $this->render('gestion_salle/modifier_salle.html.twig', [
            'role' => $role,
            'form' => $form->createView(), // Génération de la vue du formulaire
            'salle' => $salle, // Passage de l'objet salle à la vue
            'salleNom' => $salleNom,
            'salles' => $salles,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }

    #[Route("{role}/gestion-salles/supprimer/{Nom}", name: "app_gestion_salle_supprimer_confirm")]
    public function supprimerConfirmSalle(Request $request, string $role, string $Nom, SalleRepository $salleRepository, SARepository $saRepository): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        // Récupérer toutes les salles
        $salles = $salleRepository->findAll();

        return $this->render('gestion_salle/supprimer_salle.html.twig', [
            'role' => $role,
            'salle' => $salleRepository->findOneBy(['Nom' => $Nom]),
            'salles' => $salles,
            'salleNom' => $salleNom,  // Passer la salle sélectionnée à la vue
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }

    #[Route("{role}/gestion-salles/supprimer-confirm/{id}", name: "app_gestion_salle_supprimer")]
    public function supprimerSalle(Request $request, string $role, int $id, SARepository $saRepository, EntityManagerInterface $em, SalleRepository $salleRepository): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        // Récupérer la salle à supprimer
        $salle = $salleRepository->find($id);

        if (!$salle) {
            throw $this->createNotFoundException('Salle introuvable.');
        }

        // Vérifier si la salle a un SA associé
        $sa = $salle->getSA();
        if ($sa) {
            // Libérer le SA en dissociant la salle
            $sa->setSalle(null);
            // Persister cette modification
            $em->persist($sa);
        }

        // Supprimer la Salle
        $salleRepository->supprimer_Salle($salle, true);

        // Ajouter un message de confirmation
        $this->addFlash('success', 'La Salle a été supprimée avec succès.');

        // Redirection vers la liste des Salles
        return $this->redirectToRoute('app_gestion_salles', ['role' => $role]);
    }
}