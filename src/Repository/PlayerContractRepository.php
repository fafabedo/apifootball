<?php

namespace App\Repository;

use App\Entity\PlayerContract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlayerContract|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerContract|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerContract[]    findAll()
 * @method PlayerContract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerContractRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PlayerContract::class);
    }

    // /**
    //  * @return PlayerContract[] Returns an array of PlayerContract objects
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
    public function findOneBySomeField($value): ?PlayerContract
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
