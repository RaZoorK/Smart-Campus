<?php

namespace App\Repository;

use App\Entity\SA;
use App\Entity\Salle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salle>
 */
class SalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

    public function save(Salle $salle, bool $flush = false): void
    {
        $this->getEntityManager()->persist($salle); // Marque l'entité pour être persistée

        if ($flush) {
            $this->getEntityManager()->flush(); // Applique les changements dans la base de données
        }
    }

    public function ajoutSalle(Salle $salle, bool $flush = false): void
    {
        $this->getEntityManager()->persist($salle); // Marque l'entité pour être persistée

        if ($flush) {
            $this->getEntityManager()->flush(); // Applique les changements dans la base de données
        }
    }

    public function modifier_Salle(Salle $salle, bool $flush) : void{
        $this->getEntityManager()->persist($salle);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function supprimer_Salle(Salle $salle, bool $flush): void
    {
        // Supprimer l'entité en la passant à la méthode remove
        $this->getEntityManager()->remove($salle);

        if ($flush) {
            // Sauvegarder la modification dans la base de données
            $this->getEntityManager()->flush();
        }
    }

    public function salleExisteDansBatiment(string $nom, string $batiment): bool
    {
        return (bool) $this->createQueryBuilder('salle')
            ->where('salle.Nom = :nom')
            ->andWhere('salle.batiment = :batiment')
            ->setParameter('nom', $nom)
            ->setParameter('batiment', $batiment)
            ->setMaxResults(1) // Limiter à un seul résultat
            ->getQuery()
            ->getOneOrNullResult();
    }


    //    /**
    //     * @return Salle[] Returns an array of Salle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Salle
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
