<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Utilisateur;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    // src/Repository/NotificationRepository.php
    public function findRecuesByUserOrderedByDateDesc($user): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.destinataire = :user') // Notifications reçues pour un utilisateur
            ->setParameter('user', $user)
            ->orderBy('n.dateCreation', 'DESC') // Trier par date décroissante
            ->getQuery()
            ->getResult();
    }

    public function findEnvoyeesByUserOrderedByDateDesc($user): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.expediteur = :user') // Notifications envoyées par un utilisateur
            ->setParameter('user', $user)
            ->orderBy('n.dateCreation', 'DESC') // Trier par date décroissante
            ->getQuery()
            ->getResult();
    }

    public function countNotificationsNonLues(Utilisateur $utilisateur, Utilisateur $technicien): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('technicien', $technicien)
            ->where('n.destinataire = :utilisateur')
            ->andWhere('n.vue = false')
            ->andWhere('n.expediteur = :technicien')
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function findByUserWithFilters(Utilisateur $utilisateur, array $filtre, string $type): array
    {
        $qb = $this->createQueryBuilder('n');

        if ($type === 'recues') {
            $qb->andWhere('n.destinataire = :utilisateur')
                ->setParameter('utilisateur', $utilisateur);
        } elseif ($type === 'envoyees') {
            $qb->andWhere('n.expediteur = :utilisateur')
                ->setParameter('utilisateur', $utilisateur);
        } else {
            throw new \InvalidArgumentException('Type invalide : utilisez "recues" ou "envoyees".');
        }

        // Ajout des filtres
        if (!empty($filtre['sujet'])) {
            $qb->andWhere('n.sujet LIKE :sujet')
                ->setParameter('sujet', '%' . $filtre['sujet'] . '%');
        }

        // Filtrage par expéditeur
        if (!empty($filtre['expediteur'])) {
            $qb->andWhere('n.expediteur = :expediteur')
                ->setParameter('expediteur', $filtre['expediteur']);
        }

        // Filtrage par destinataire
        if (!empty($filtre['destinataire'])) {
            $qb->andWhere('n.destinataire = :destinataire')
                ->setParameter('destinataire', $filtre['destinataire']);
        }

        // Filtrage par destinataire
        if (!empty($filtre['message'])) {
            $qb->andWhere('n.message LIKE :message')
                ->setParameter('message', '%' . $filtre['message'] . '%');
        }

        // Filtrage par destinataire
        if (isset($filtre['vue'])) {
            if ($filtre['vue']) {
                // Si la case est cochée, nous filtrons pour les notifications non vues
                $qb->andWhere('n.vue = :vue')
                    ->setParameter('vue', false);
            }
        }

        // Filtrage par destinataire
        if (!empty($filtre['dateFilter'])) {
            $date = new \DateTime();
            switch ($filtre['dateFilter']) {
                case 'moins_2_jours':
                    $date->modify('-2 days');
                    $qb->andWhere('n.dateCreation >= :date');
                    break;
                case 'moins_5_jours':
                    $date->modify('-5 days');
                    $qb->andWhere('n.dateCreation >= :date');
                    break;
                case 'plus_5_jours':
                    $date->modify('-5 days');
                    $qb->andWhere('n.dateCreation < :date');
                    break;
            }
            $qb->setParameter('date', $date);
        }

        // Tri par date décroissante
        $qb->orderBy('n.dateCreation', 'DESC');

        return $qb->getQuery()->getResult();
    }




    //    /**
    //     * @return Notification[] Returns an array of Notification objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Notification
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
