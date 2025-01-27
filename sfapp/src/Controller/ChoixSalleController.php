<?php

namespace App\Controller;

use App\Form\SalleChoiceType;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\LectureFichierService;
use App\Service\JsonDecoderService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChoixSalleController extends AbstractController
{
    #[Route('{role}/choix-salles', name: 'app_choix_salles')]
    public function index(string $role, SalleRepository $salleRepository, LectureFichierService $lectureFichierService, JsonDecoderService $serviceDecodeurJson, Request $request, HttpClientInterface $httpClient): Response
    {
        $utilisateur = null;
        if ($role !== 'Occupant') {
            $utilisateur = $this->getUser();
            if ($utilisateur->getFonction() === 'Technicien' && $role === 'Manager') {
                $this->get('session')->getFlashBag()->clear();
                $this->addFlash(
                    'error',
                    'Accès interdit : les techniciens ne peuvent pas accéder à la page des managers. Vous avez été redirigé vers la page de connexion du technicien.'
                );

                return $this->redirectToRoute('app_se_connecter', ['role' => 'Technicien']);
            }
        }
        $salleNom = $request->getSession()->get('salleNom', 'Secrétariat'); // Valeur par défaut si aucune salle n'est stockée



        // Préparation des filtres pour le formulaire
        $batiments = $salleRepository->createQueryBuilder('s')
            ->select('DISTINCT s.batiment')
            ->getQuery()
            ->getResult();

        $choix_batiments = [];
        foreach ($batiments as $batiment) {
            $choix_batiments[$batiment['batiment']] = $batiment['batiment'];
        }

        $etages = $salleRepository->createQueryBuilder('s')
            ->select('DISTINCT s.etage')
            ->orderBy('s.etage', 'ASC')
            ->getQuery()
            ->getResult();

        $choix_etages = [];
        foreach ($etages as $etage) {
            $choix_etages['Étage ' . $etage['etage']] = $etage['etage'];
        }

        $form = $this->createForm(SalleChoiceType::class, null, [
            'choix_batiments' => $choix_batiments,
            'choix_etages' => $choix_etages,
        ]);
        $form->handleRequest($request);

        $queryBuilder = $salleRepository->createQueryBuilder('s')
            ->innerJoin('s.SA', 'sa')
            ->where('sa.id IS NOT NULL');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!empty($data->getNom())) {
                $queryBuilder->andWhere('s.Nom LIKE :nom')
                    ->setParameter('nom', '%' . $data->getNom() . '%');
            }

            if (!empty($data->getEtage()) || $data->getEtage() === '0') {
                $queryBuilder->andWhere('s.etage = :etage')
                    ->setParameter('etage', $data->getEtage());
            }

            if (!empty($data->getBatiment())) {
                $queryBuilder->andWhere('s.batiment = :batiment')
                    ->setParameter('batiment', $data->getBatiment());
            }
        }

        $salles = $queryBuilder->getQuery()->getResult();

        // Tableau des configurations pour chaque base de données
        $sallesConfig = [
            ['dbname' => 'sae34bdk1eq1', ],
            ['dbname' => 'sae34bdk1eq2', ],
            ['dbname' => 'sae34bdk1eq3', ],
            ['dbname' => 'sae34bdk2eq1', ],
            ['dbname' => 'sae34bdk2eq2', ],
            ['dbname' => 'sae34bdk2eq3', ],
            ['dbname' => 'sae34bdl1eq1', ],
            ['dbname' => 'sae34bdl1eq2', ],
            ['dbname' => 'sae34bdl1eq3', ],
            ['dbname' => 'sae34bdl2eq1', ],
            ['dbname' => 'sae34bdl2eq2', ],
            ['dbname' => 'sae34bdl2eq3', ],
            ['dbname' => 'sae34bdm1eq1', ],
            ['dbname' => 'sae34bdm1eq2', ],
            ['dbname' => 'sae34bdm1eq3', ],
        ];


        // Définir les dates de début et de fin de l'intervalle (par exemple, les 24 dernières heures)
        $now = new \DateTime();  // Date et heure actuelles
        $dateFin = $now->modify('+1 day')->format('Y-m-d');  // Format de la date pour la fin de l'intervalle

        // La période des valeurs sur les 24 heures qui arrivent
        $dateDebut = $now->modify('-1 month')->format('Y-m-d');

        // Tableau pour accumuler toutes les données collectées
        $donneesTotales = [];

        // URL de l'API avec les paramètres pour récupérer les captures dans un intervalle de temps
        $urlApi = 'https://sae34.k8s.iut-larochelle.fr/api/captures/interval';
        // Récupération des données depuis l'API
        // Boucle sur chaque base de données
        foreach ($sallesConfig as $sallesBD) {
            $response = $httpClient->request('GET', $urlApi, [
                'headers' => [
                    'dbname' => $sallesBD['dbname'],
                    'username' => 'l2eq1',
                    'userpass' => 'Bemvat-taxxoj-1jyrbi',
                ],
                'query' => [
                    'date1' => $dateDebut,        // Paramètre de date de début
                    'date2' => $dateFin,
                ]
            ]);

            // Récupération du contenu JSON depuis l'API
            $contenuJson = $response->getContent();
            // Décodage des données JSON
            $donnees = $serviceDecodeurJson->decode($contenuJson);
            // Ajouter les données au tableau principal
            if (is_array($donnees)) {
                $donneesTotales = array_merge($donneesTotales, $donnees);
            }
        }

        // Charger les données JSON existantes
        $cheminFichierJson = __DIR__ . '/../../public/donnee/donnees.json';
        if (file_exists($cheminFichierJson)) {
            $donneesExistantes = $serviceDecodeurJson->decode($lectureFichierService->readFile($cheminFichierJson));
            if (!is_array($donneesExistantes)) {
                $donneesExistantes = [];
            }
        } else {
            $donneesExistantes = [];
        }

        $indexDonneesExistantes = [];
        foreach ($donneesExistantes as $donnee) {
            $cleUnique = $donnee['localisation'] . '|' . $donnee['nom'] . '|' . $donnee['dateCapture'];
            $indexDonneesExistantes[$cleUnique] = $donnee;
        }

        foreach ($donneesTotales as $donneeNouvelle) {
            $cleUnique = $donneeNouvelle['localisation'] . '|' . $donneeNouvelle['nom'] . '|' . $donneeNouvelle['dateCapture'];
            $indexDonneesExistantes[$cleUnique] = $donneeNouvelle; // Met à jour ou ajoute une nouvelle entrée
        }

        $donneesMiseAJour = array_values($indexDonneesExistantes);

        file_put_contents($cheminFichierJson, json_encode($donneesMiseAJour, JSON_PRETTY_PRINT));

        if (empty($donneesExistantes)) {
            throw new \Exception("3");
        }
        // Tri des données par date
        usort($donneesExistantes, fn($a, $b) => strtotime($b['dateCapture']) - strtotime($a['dateCapture']));

        if (empty($donneesExistantes)) {
            throw new \Exception("4");
        }
        $contenuJson = $lectureFichierService->readFile($cheminFichierJson);
        $donnees = $serviceDecodeurJson->decode($contenuJson);

        // Fonction pour récupérer la dernière valeur par type de donnée et nom de salle
        $getLastValueForSalleAndType = function ($donnees, $nomSalle, $type, $enMaintenance) {
            if ($enMaintenance) {
                // Si le SA est en maintenance, on retourne N/A pour toutes les valeurs
                return 'N/A';
            }

            // Filtrer les données par 'localisation' et type de donnée ('temp', 'hum', 'co2')
            $donneesfiltrees = array_filter($donnees, fn($d) => $d['localisation'] === $nomSalle && $d['nom'] === $type);

            // Trier par date de capture pour récupérer la plus récente
            usort($donneesfiltrees, fn($a, $b) => strtotime($b['dateCapture']) - strtotime($a['dateCapture']));

            // Retourner la valeur de la première entrée (la plus récente)
            return !empty($donneesfiltrees) ? $donneesfiltrees[0]['valeur'] : 'N/A';
        };

        // Associer les dernières données à chaque salle par nom et type
        foreach ($salles as $salle) {
            $nomSalle = $salle->getNom();
            $enMaintenance = $salle->getSA()->getEtat() && $salle->getSA()->getEtatValue() === 'En maintenance';

            $salle->temp = $getLastValueForSalleAndType($donnees, $nomSalle, 'temp', $enMaintenance);
            $salle->humidite = $getLastValueForSalleAndType($donnees, $nomSalle, 'hum', $enMaintenance);
            $salle->co2 = $getLastValueForSalleAndType($donnees, $nomSalle, 'co2', $enMaintenance);
        }

        return $this->render('choix_salle/index.html.twig', [
            'role' => $role,
            'salleNom' => $salleNom,
            'salles' => $salles,
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur ? $utilisateur->getPrenom() . " " . $utilisateur->getNom() : null,
        ]);
    }

    #[Route('{role}/redirect_donnees/{salle}', name: 'app_redirect_to_donnees')]
    public function redirectToDonnes(string $salle, string $role): Response
    {
        return $this->redirectToRoute('app_donnees', [
            'salle' => $salle,
            'role' => $role,
        ]);
    }
}
