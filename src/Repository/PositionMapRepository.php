<?php

namespace App\Repository;

use App\Entity\PositionMap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PositionMap|null find($id, $lockMode = null, $lockVersion = null)
 * @method PositionMap|null findOneBy(array $criteria, array $orderBy = null)
 * @method PositionMap[]    findAll()
 * @method PositionMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PositionMapRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PositionMap::class);
    }

    /**
     * @param $top
     * @param $left
     * @return PositionMap[]
     */
    public function findByRelativePosition($top, $left)
    {
        return $this->createQueryBuilder('pm')
            ->andWhere('pm.topPosition > :top')
            ->andWhere('pm.bottomPosition <= :top')
            ->andWhere('pm.leftPosition > :left')
            ->andWhere('pm.rightPosition <= :left')
            ->setParameter('top', $top)
            ->setParameter('left', $left)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return PositionMap[] Returns an array of PositionMap objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PositionMap
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
