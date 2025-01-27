<?php

namespace App\Repository;

use App\Entity\SA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SA>
 */
class SARepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SA::class);
    }

    public function save(SA $SA, bool $flush) : void{
        $this->getEntityManager()->persist($SA);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function AjoutSA(SA $SA, bool $flush) : void{
        $this->getEntityManager()->persist($SA);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function supprimer_SA(SA $sa, bool $flush) : bool{
        // Supprimer l'entité en la passant à la méthode remove
        $this->getEntityManager()->remove($sa);

        if ($flush) {
            // Sauvegarder la modification dans la base de données
            $this->getEntityManager()->flush();
            return true;
        }
        return false;
    }

    public function modifier_SA(SA $sa, bool $flush) : void{
        $this->getEntityManager()->persist($sa);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findNonAffectes()
    {
        return $this->createQueryBuilder('sa')
            ->leftJoin('sa.salle', 'salle')
            ->where('salle IS NULL') // Filtrer les SA qui ne sont pas associés à une salle
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return SA[] Returns an array of SA objects
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

    //    public function findOneBySomeField($value): ?SA
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
