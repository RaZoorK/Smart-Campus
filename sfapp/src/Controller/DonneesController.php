<?php
namespace App\Controller;

use App\Entity\Salle;
use App\Repository\SalleRepository;
use App\Repository\SeuilRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\LectureFichierService;
use App\Service\JsonDecoderService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Seuil;
use App\Form\SeuilType;

class DonneesController extends AbstractController
{
    #[Route('{role}/donnees/{salle}', name: 'app_donnees')]
    public function index(
        string                 $role,
        string                 $salle,
        SalleRepository        $salleRepository,
        Request                $requete,
        LectureFichierService  $serviceLectureFichier,
        JsonDecoderService     $serviceDecodeurJson,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $utilisateur = null;
        if ($role !== 'Occupant')
        {
            // Récupération de l'utilisateur connecté
            $utilisateur = $this->getUser();
            // Vérification des droits d'accès
            if ($utilisateur->getFonction() === 'Technicien' && $role === 'Manager') {
                $this->addFlash(
                    'erreur',
                    'Accès interdit : les techniciens ne peuvent pas accéder à la page des managers. Vous avez été redirigé vers la page de connexion du technicien.'
                );

                return $this->redirectToRoute('app_se_connecter', ['role' => 'Technicien']);
            }
        }

        // Récupération de toutes les salles
        $salles = $salleRepository->findAll();

        // Récupérer tous les seuils
        $seuils = $entityManager->getRepository(Seuil::class)->findAll();

        // Récupérer les seuils pour chaque type
        $seuilTemp = $seuils[0] ?? null;  // Exemple, à ajuster selon vos données
        $seuilHum = $seuils[1] ?? null;
        $seuilCo2 = $seuils[2] ?? null;

        // Gestion de la salle sélectionnée
        $salleNom = $requete->query->get('salleNom', $salle);
        $salleValide = array_filter($salles, fn($s) => $s->getNom() === $salleNom);

        if (empty($salleValide)) {
            $salleNom = 'Secrétariat'; // Salle par défaut
        }

        // Sauvegarder la salle sélectionnée dans la session
        $session = $requete->getSession();
        $session->set('salleNom', $salleNom);

        // Redirection si une nouvelle salle a été sélectionnée
        if ($salleNom !== $salle) {
            return $this->redirectToRoute('app_donnees', [
                'role' => $role,
                'salle' => $salleNom,
            ]);
        }

        // Lecture et décodage des données JSON
        $cheminJson = __DIR__ . '/../../public/donnee/donnees.json';
        $contenuJson = $serviceLectureFichier->readFile($cheminJson);
        $donnees = $serviceDecodeurJson->decode($contenuJson);

        // Tri des données par date
        usort($donnees, fn($a, $b) => strtotime($b['dateCapture']) - strtotime($a['dateCapture']));

        // Dernière entrée (la plus récente)
        $derniereEntree = $donnees[0];

        // Calcul des moyennes par jour et par capteur
        $moyenneParJour = [];
        $joursDeLaSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        foreach ($donnees as $entree) {
            // Filtrage en fonction du capteur et de la salle
            if ($entree['localisation'] !== $salleNom) {
                continue;
            }

            $date = new \DateTime($entree['dateCapture']);
            $cleDate = $date->format('Y-m-d');
            $jourDeLaSemaine = $joursDeLaSemaine[$date->format('w')];

            // Initialisation de la structure pour chaque jour
            if (!isset($moyenneParJour[$cleDate])) {
                $moyenneParJour[$cleDate] = [
                    'totalTemp' => 0,
                    'totalHumidite' => 0,
                    'totalCo2' => 0,
                    'compteurTemp' => 0,
                    'compteurHumidite' => 0,
                    'compteurCo2' => 0,
                    'jourDeLaSemaine' => $jourDeLaSemaine,
                ];
            }

            // Agrégation des valeurs par jour pour chaque type
            if ($entree['nom'] === 'temp') {
                $moyenneParJour[$cleDate]['totalTemp'] += (float)$entree['valeur'];
                $moyenneParJour[$cleDate]['compteurTemp']++;
            } elseif ($entree['nom'] === 'hum') {
                $moyenneParJour[$cleDate]['totalHumidite'] += (float)$entree['valeur'];
                $moyenneParJour[$cleDate]['compteurHumidite']++;
            } elseif ($entree['nom'] === 'co2') {
                $moyenneParJour[$cleDate]['totalCo2'] += (float)$entree['valeur'];
                $moyenneParJour[$cleDate]['compteurCo2']++;
            }
        }

        // Calcul des moyennes finales
        foreach ($moyenneParJour as $cleDate => &$donneesJour) {
            $donneesJour['moyenneTemp'] = $donneesJour['compteurTemp'] > 0 ? $donneesJour['totalTemp'] / $donneesJour['compteurTemp'] : null;
            $donneesJour['moyenneHum'] = $donneesJour['compteurHumidite'] > 0 ? $donneesJour['totalHumidite'] / $donneesJour['compteurHumidite'] : null;
            $donneesJour['moyenneCo2'] = $donneesJour['compteurCo2'] > 0 ? $donneesJour['totalCo2'] / $donneesJour['compteurCo2'] : null;
        }

        // Données du jour précédent
        $jourPrecedent = date('Y-m-d', strtotime('-1 day', strtotime($derniereEntree['dateCapture'])));
        $donneesJourPrecedent = $moyenneParJour[$jourPrecedent] ?? null;

        // Tri des données pour obtenir les 7 derniers jours
        $trieParDate = array_slice($moyenneParJour, 0, 7, true);
        // Prendre les 7 premiers jours après tri
        $derniereSemaine = array_reverse($trieParDate);
        // Inverser pour afficher du plus récent au plus ancien

        // Calcul des différences par rapport au jour précédent
        $diffTemperature = $donneesJourPrecedent ? $derniereEntree['valeur'] - $donneesJourPrecedent['moyenneTemp'] : null;
        $diffHumidite = $donneesJourPrecedent ? $derniereEntree['valeur'] - $donneesJourPrecedent['moyenneHum'] : null;
        $diffCo2 = $donneesJourPrecedent ? $derniereEntree['valeur'] - $donneesJourPrecedent['moyenneCo2'] : null;

        // Récupérer la date du dernier enregistrement
        $dateDerniereEntree = (new \DateTime($derniereEntree['dateCapture']))->format('Y-m-d');

        // Filtrer les données pour le dernier jour
        $donneesPourGraphique = array_filter($donnees, fn($entree) => (new \DateTime($entree['dateCapture']))->format('Y-m-d') === $dateDerniereEntree);

        // Récupérer les 30 derniers jours triés
        $trierParMois = array_slice($moyenneParJour, 0, 30, true);

        $dernierMois = array_reverse($trierParMois);


        return $this->render('donnees/index.html.twig', [
            'role' => $role,
            'salleNom' => $salleNom,
            'salles' => $salles,
            'salle' => $salle,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur ? $utilisateur->getPrenom() . " " . $utilisateur->getNom() : null,
            'donnees' => $donnees,
            'moyenneParJour' => $moyenneParJour,
            'derniereSemaine' => $derniereSemaine,
            'dernierMois' => $dernierMois,
            'diffTemperature' => $diffTemperature,
            'diffHumidite' => $diffHumidite,
            'diffCo2' => $diffCo2,
            'moyenneTemp' => $previousDayData['moyenneTemp'] ?? null,
            'moyenneHum' => $previousDayData['moyenneHum'] ?? null,
            'moyenneCo2' => $previousDayData['moyenneCo2'] ?? null,
            'seuils' => $seuils,
            'seuilTemp' => $seuilTemp,
            'seuilHum' => $seuilHum,
            'seuilCo2' => $seuilCo2,
            'donneesPourGraphique' => $donneesPourGraphique,
        ]);
    }

    #[Route('/redirect_gestion_sa/{role}/{salle}', name: 'redirect_to_gestion_sa')]
    public function redirigerVersGestionSA(string $role, string $salle): Response
    {
        return $this->redirectToRoute('app_gestion_sa', [
            'role' => $role,
            'salle' => $salle,
        ]);
    }

    #[Route('{role}/parametres', name: 'app_parametres')]
    public function modifierSeuil(
        string                 $role,
        Request                $request,
        EntityManagerInterface $entityManager,
        SalleRepository        $salleRepository,
        Request                $requete,
    ): Response
    {
        $utilisateur = null;
        $session = $request->getSession();
        $salleNom = $session->get('salleNom', 'C007'); // Valeur par défaut
        if ($role !== 'Occupant') {
            // Récupération de l'utilisateur connecté
            $utilisateur = $this->getUser();
            // Vérification des droits d'accès
            if ($utilisateur->getFonction() === 'Technicien' && $role === 'Manager') {
                $this->addFlash(
                    'erreur',
                    'Accès interdit : les techniciens ne peuvent pas accéder à la page des managers. Vous avez été redirigé vers la page de connexion du technicien.'
                );

                return $this->redirectToRoute('app_se_connecter', ['role' => 'Technicien']);
            }
        }

        // Récupération de toutes les salles
        $salles = $salleRepository->findAll();
        $seuils = $entityManager->getRepository(Seuil::class)->findAll();

        // Initialisation des variables
        $seuilActuel = null;
        $typesDisponibles = ['Température', 'Humidité', 'CO2'];

        // Gestion de la sélection du type
        $typeSelectionne = $request->query->get('type', 'Température'); // Valeur par défaut "Température"

        // Chercher le seuil pour le type sélectionné
        foreach ($seuils as $seuil) {
            if ($seuil->getType() === $typeSelectionne) {
                $seuilActuel = $seuil;
                break;
            }
        }

        // Créer un formulaire pour le seuil sélectionné
        $form = $this->createForm(SeuilType::class, $seuilActuel);
        $form->handleRequest($request);

        // Sauvegarder les données si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // L'entité est validée ici, et vous pouvez persister les données
            $entityManager->persist($seuilActuel);
            $entityManager->flush();

            // Rediriger vers la page des données de la salle après soumission
            return $this->redirectToRoute('app_donnees', [
                'role' => $role,
                'salle' => $salleNom,
            ]);
        }

        // Si la validation échoue, afficher les erreurs
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash(
                'erreur',
                'Les seuils sont invalides. Veuillez vérifier les valeurs.'
            );
        }

        // Passer les variables au template
        return $this->render('donnees/parametres.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
            'salles' => $salles,
            'typesDisponibles' => $typesDisponibles,
            'typeSelectionne' => $typeSelectionne,
            'seuilActuel' => $seuilActuel,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur ? $utilisateur->getPrenom() . " " . $utilisateur->getNom() : null,
            'salleNom' => $salleNom,
        ]);


    }
}