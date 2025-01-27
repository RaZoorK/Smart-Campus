<?php
namespace App\Controller;

use App\Entity\SA;
use App\Form\AjoutSAType;
use App\Form\ModifSAType;
use App\Form\SaFilterType;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GestionSAController extends AbstractController
{
    #[Route('/{role}/gestion-sa', name: 'app_gestion_sa')]
    public function index(EntityManagerInterface $em, Request $request, string $role, SalleRepository $salleRepository): Response
    {
        $utilisateur = $this->getUser();

        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        // Créez le formulaire de recherche
        $form = $this->createForm(SaFilterType::class);
        $form->handleRequest($request);

        // Récupérez les données en fonction du filtre
        $queryBuilder = $em->getRepository(SA::class)->createQueryBuilder('sa');

        // Ajouter le tri par nomSA en ordre croissant (ASC)
        $queryBuilder->orderBy('sa.nomSA', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $donnnees = $form->getData();

            // Si 'nomSA' est présent dans le formulaire, on applique le filtre
            if (!empty($donnnees['nomSA'])) {
                $nomSA = $donnnees['nomSA'];

                // Assurez-vous que vous passez le bon type à la requête
                // Si 'nomSA' est une chaîne de caractères, vous n'avez rien à faire ici
                $queryBuilder->andWhere('sa.nomSA = :nomSA')
                    ->setParameter('nomSA', $nomSA);
            }
        }

        // Exécutez la requête pour récupérer les résultats
        $sas = $queryBuilder->getQuery()->getResult();

        // Récupérer toutes les salles
        $salles = $salleRepository->findAll();

        // Rendre la vue avec la salle sélectionnée et les salles disponibles
        return $this->render('gestion_sa/index.html.twig', [
            'sas' => $sas,
            'form' => $form->createView(),
            'role' => $role,
            'salles' => $salles,
            'salleNom' => $salleNom,  // Passer la salle sélectionnée à la vue
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);


    }


    #[Route("{role}/gestion-sa/ajouter", name: "app_gestion_sa_ajouter")]

    public function ajouterSA(Request $request, string $role, SARepository $SARepository, SalleRepository $salleRepository) : Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007');
        $sa = new SA();
        $form = $this->createForm(AjoutSAType::class, $sa, [
            "method" => "POST",
        ]);
        $form->handleRequest($request);


        // Récupérer toutes les salles
        $salles = $salleRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $saExistant = $SARepository->findOneBy(['nomSA' => $sa->getNomSA()]);
            if ($saExistant) {
                $this->addFlash('error', 'Un SA avec ce nom existe déjà.');
                return $this->redirectToRoute('app_gestion_sa_ajouter', ['role' => $role]);
            }
            $SARepository->AjoutSA($sa, true);
            return $this->redirectToRoute('app_gestion_sa', ['role' => $role]);
        }

        return $this->render('gestion_sa/ajouterSA.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
            'salles' => $salles,
            'salleNom' => $salleNom,  // Passer la salle sélectionnée à la vue
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }


    #[Route("{role}/gestion-sa/modifier/{nomSA}", name: "app_gestion_sa_modifier")]
    public function modifierSA(Request $request, string $role, string $nomSA, SARepository $SARepository, SalleRepository $SalleRepository): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        // Récupérer l'entité SA à modifier
        $sa = $SARepository->findOneBy(['nomSA' => $nomSA]);

        if (!$sa) {
            throw $this->createNotFoundException("SA avec le nom $nomSA n'existe pas.");
        }
        $ancienneSalle = $sa->getSalle();

        // Créer le formulaire pour modifier l'entité
        $form = $this->createForm(ModifSAType::class, $sa, [
            "method" => "POST",
            "saActuel" => $sa, // Passez l'objet SA comme option
        ]);
        $form->handleRequest($request);

        // Récupérer toutes les salles
        $salles = $SalleRepository->findAll();

        // Gérer la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un autre SA avec le même nom existe déjà
            $saExistant = $SARepository->findOneBy(['nomSA' => $sa->getNomSA()]);

            // Vérifier si les enregistrements existants ne correspondent pas à l'entité actuelle
            if ($saExistant && $saExistant->getId() !== $sa->getId()){
                $this->addFlash('error', 'Un SA avec ce nom existe déjà.');

                // Redirection vers le formulaire
                return $this->redirectToRoute('app_gestion_sa_modifier', [
                    'role' => $role,
                    'nomSA' => $nomSA,
                    'salles' => $salles,
                    'salleNom' => $salleNom,  // Passer la salle sélectionnée à la vue
                ]);
            }

            // Vérifier si une salle est modifiée
            $salle = $sa->getSalle();
            if ($salle) {
                // Si une nouvelle salle est associée
                $salleExistantes = $SalleRepository->find($salle->getId());
                if($ancienneSalle != NULL){
                    $ancienneSalle->setSA(NULL);
                }

                // Si la salle existe déjà, on évite de la persister à nouveau
                if ($salleExistantes) {
                    // Ne rien faire, la salle est déjà persistée
                    $sa->setSalle($salleExistantes);
                } else {
                    // Sinon, la salle est une nouvelle entité et doit être persistée
                    $SalleRepository->save($salle, true);
                }
            }

            // Persister les modifications dans la base de données
            $SARepository->modifier_SA($sa, true);

            // Redirection après la mise à jour
            $this->addFlash('success', 'SA modifié avec succès.');
            return $this->redirectToRoute('app_gestion_sa', ['role' => $role]);
        }

        // Retourner la vue du formulaire avec les données de l'entité
        return $this->render('gestion_sa/modifierSA.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
            'sa' => $sa,
            'salles' => $salles,
            'salleNom' => $salleNom,  // Passer la salle sélectionnée à la vue
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }


    #[Route("{role}/gestion-sa/supprimer/{nomSA}", name: "app_gestion_sa_supprimer")]
    public function supprimerSA(Request $request, string $role, string $nomSA, SARepository $SARepository, EntityManagerInterface $em, SalleRepository $salleRepository): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        // Récupérer le SA à supprimer
        $sa = $SARepository->findOneBy(['nomSA' => $nomSA]);
        if (!$sa) {
            throw $this->createNotFoundException('SA introuvable.');
        }

        // Vérifier si le SA a une salle associée
        $salle = $sa->getSalle();
        if ($salle) {
            // Libérer la salle en dissociant le SA
            $salle->setSA(null);
            // Persister cette modification
            $em->persist($salle);
        }

        // Supprimer le SA
        $SARepository->supprimer_SA($sa, true);

        // Ajouter un message de confirmation
        $this->addFlash('success', 'Le SA a été supprimé avec succès.');

        // Récupérer toutes les salles
        $salles = $salleRepository->findAll();

        // Redirection vers la liste des SA
        return $this->redirectToRoute('app_gestion_sa', ['role' => $role]);
    }

    #[Route("{role}/gestion-sa/supprimer-confirm/{nomSA}", name: "app_gestion_sa_supprimer_confirm")]
    public function supprimerConfirmSA(Request $request, string $role, string $nomSA, SARepository $sa, SalleRepository $salleRepository): Response
    {
        $utilisateur = $this->getUser();
        // Récupérer la salle sélectionnée depuis la session
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        // Récupérer toutes les salles
        $salles = $salleRepository->findAll();

        return $this->render('gestion_sa/supprimerSA.html.twig', [
            'role' => $role,
            'sa' => $sa->findOneBy(['nomSA' => $nomSA]),
            'salles' => $salles,
            'salleNom' => $salleNom,  // Passer la salle sélectionnée à la vue
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur->getPrenom() . ' ' . $utilisateur->getNom(),
        ]);
    }
}