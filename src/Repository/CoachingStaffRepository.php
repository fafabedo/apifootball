<?php

namespace App\Repository;

use App\Entity\CoachingStaff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CoachingStaff|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoachingStaff|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoachingStaff[]    findAll()
 * @method CoachingStaff[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoachingStaffRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CoachingStaff::class);
    }

    // /**
    //  * @return CoachingStaff[] Returns an array of CoachingStaff objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CoachingStaff
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
