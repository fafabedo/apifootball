<?php

namespace App\Repository;

use App\Entity\Federation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Federation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Federation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Federation[]    findAll()
 * @method Federation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FederationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Federation::class);
    }

    // /**
    //  * @return Federation[] Returns an array of Federation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Federation
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
