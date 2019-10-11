<?php

namespace App\Repository;

use App\Entity\MatchLineup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MatchLineup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MatchLineup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MatchLineup[]    findAll()
 * @method MatchLineup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchLineupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MatchLineup::class);
    }

    // /**
    //  * @return MatchLineup[] Returns an array of MatchLineup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MatchLineup
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
