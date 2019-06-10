<?php

namespace App\Repository;

use App\Entity\PlayerMarketValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlayerMarketValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerMarketValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerMarketValue[]    findAll()
 * @method PlayerMarketValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerMarketValueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PlayerMarketValue::class);
    }

    // /**
    //  * @return PlayerMarketValue[] Returns an array of PlayerMarketValue objects
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
    public function findOneBySomeField($value): ?PlayerMarketValue
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
