<?php

namespace App\Repository;

use App\Entity\TeamCoachingStaff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TeamCoachingStaff|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamCoachingStaff|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamCoachingStaff[]    findAll()
 * @method TeamCoachingStaff[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamCoachingStaffRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TeamCoachingStaff::class);
    }

    // /**
    //  * @return TeamCoachingStaff[] Returns an array of TeamCoachingStaff objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TeamCoachingStaff
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
