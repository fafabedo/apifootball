<?php

namespace App\Repository;

use App\Entity\PlayerMaketValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlayerMaketValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerMaketValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerMaketValue[]    findAll()
 * @method PlayerMaketValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerMaketValueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PlayerMaketValue::class);
    }

    // /**
    //  * @return PlayerMaketValue[] Returns an array of PlayerMaketValue objects
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
    public function findOneBySomeField($value): ?PlayerMaketValue
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
