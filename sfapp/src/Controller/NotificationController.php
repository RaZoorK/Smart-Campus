<?php
namespace App\Controller;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use App\Form\AnomalieType;
use App\Model\TypeNotif;
use App\Repository\NotificationRepository;
use App\Repository\SalleRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\FiltreNotifType;
class NotificationController extends AbstractController
{

    #[Route('/{role}/anomalie', name: 'app_anomalie')]
    public function anomalie(
        Request $request,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository,
        SalleRepository $salleRepository,
        string $role
    ): Response {
        $utilisateur = null;
        $expediteurNom = null;
        $expediteurPrenom = null;
        if ($role !== 'Occupant')
        {
            $utilisateur = $this->getUser();
        }
        $salles = $salleRepository->findAll();
        $salleNom = $request->getSession()->get('salleNom', 'C007'); // Valeur par défaut si aucune salle n'est stockée

        $notification = new Notification();
        $notification->setExpediteur($utilisateur);
        $notification->setDateCreation(new \DateTimeImmutable());
        $notification->setVue(false);
        $notification->setTypeNotif(TypeNotif::ANOMALIE);
        $notification->setDestinataire($utilisateurRepository->findOneBy(['fonction' => 'Manager']));


        // Création du formulaire
        $form = $this->createForm(AnomalieType::class, $notification, [
            'role' => $role,
        ]);
        $form->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $destinataire = $notification->getDestinataire();

            // Mise à jour du champ nbNotif
            $destinataire->setNbNotif(($destinataire->getNbNotif() ?? 0) + 1);
            if ($role === 'Occupant')
            {
                $expediteurNom = $notification->setExpediteurNom($form->get('expediteurNom')->getData());
                $expediteurPrenom = $notification->setExpediteurPrenom($form->get('expediteurPrenom')->getData());
            }
            // Persister les entités
            $entityManager->persist($notification);
            $entityManager->persist($destinataire);
            $entityManager->flush();

            // Ajouter un message flash
            $this->addFlash('success', 'Anomalie envoyée avec succès.');

            // Redirection vers la page d'envoi
            return $this->redirectToRoute('app_anomalie', ['role' => $role]);
        }

        // Rendu du formulaire
        return $this->render('notification/anomalie.html.twig', [
            'form' => $form->createView(),
            'salles' => $salles, // Passer les salles obtenues via la recherche
            'role' => $role,
            'salleNom' => $salleNom,
            'utilisateur' => $utilisateur,
            'expediteurNom' => $expediteurNom,
            'expediteurPrenom' => $expediteurPrenom,
            'nomUtilisateur' => $utilisateur ? $utilisateur->getPrenom() . ' ' . $utilisateur->getNom() : null,
            'type' => $notification->getTypeNotif(),
        ]);
    }

    #[Route('/{role}/notification', name: 'app_notification')]
    public function notification(
        Request $request,
        string $role,
        SalleRepository $salleRepository,
        NotificationRepository $notificationRepository): Response
    {
        $utilisateur = null;
        if ($role !== 'Occupant')
        {
            $utilisateur = $this->getUser();
        }
        $salles = $salleRepository->findAll();
        $salleNom = $request->getSession()->get('salleNom', 'Secrétariat');

        // Initialiser le formulaire de filtres
        $form = $this->createForm(FiltreNotifType::class);
        $form->handleRequest($request);

        // Notifications par défaut
        $notificationsRecues = [];
        $notificationsEnvoyees = [];

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les filtres depuis le formulaire
            $filtre = $form->getData();

            // Appliquer les filtres sur les notifications reçues et envoyées
            $notificationsRecues = $notificationRepository->findByUserWithFilters($utilisateur, $filtre, 'recues');
            $notificationsEnvoyees = $notificationRepository->findByUserWithFilters($utilisateur, $filtre, 'envoyees');
        } else {
            // Récupérer les notifications par défaut (sans filtres)
            $notificationsRecues = $notificationRepository->findRecuesByUserOrderedByDateDesc($utilisateur);
            $notificationsEnvoyees = $notificationRepository->findEnvoyeesByUserOrderedByDateDesc($utilisateur);
        }
        return $this->render('notification/notification.html.twig', [
            'form' => $form->createView(),
            'salles' => $salles, // Passer les salles obtenues via la recherche
            'role' => $role,
            'salleNom' => $salleNom,
            'utilisateur' => $utilisateur,
            'nomUtilisateur' => $utilisateur ? $utilisateur->getPrenom() . ' ' . $utilisateur->getNom() : null,
            'notificationsRecues' => $notificationsRecues,
            'notificationsEnvoyees' => $notificationsEnvoyees,
        ]);
    }

    #[Route('{role}/notification/{id}/marquer-vue', name: 'notification_marquer_vue', methods: ['POST'])]
    public function marquerVue(
        Notification $notification,
        EntityManagerInterface $entityManager,
        string $role,
    ): Response {

        $utilisateur = $this->getUser();
        // Vérifier que l'utilisateur est le destinataire de la notification
        if ($notification->getDestinataire() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier cette notification.");
        }

        // Marquez la notification comme vue
        $notification->setVue(true);
        $utilisateur->setNbNotif(($utilisateur->getNbNotif() ?? 0) - 1 );

        $entityManager->flush();

        // Ajoutez un message flash
        $this->addFlash('success', 'Notification marquée comme vue.');

        // Redirigez vers la page de notification
        return $this->redirectToRoute('app_notification', ['role' => $role]);
    }

    #[Route('{role}/notification/marquerToutesVues', name: 'notification_marquer_toutes_vues', methods: ['POST'])]
    public function marquerToutesVues(
        string $role,
        EntityManagerInterface $entityManager
    ): Response {
        $utilisateur = $this->getUser();

        // Récupérer toutes les notifications non lues
        $notifications = $utilisateur->getNotificationsReçues()->filter(function ($notification) {
            return !$notification->isVue();
        });

        foreach ($notifications as $notification) {
            $notification->setVue(true);
        }

        $utilisateur->setNbNotif(0);

        $entityManager->flush();

        $this->addFlash('success', 'Toutes les notifications ont été marquées comme lues.');        $this->addFlash('success', 'Toutes les notifications ont été marquées comme lues.');

        return $this->redirectToRoute('app_notification', ['role' => $role]);
    }
}
?>