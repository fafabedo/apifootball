<?php

namespace App\Repository;

use App\Entity\TeamType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TeamType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamType[]    findAll()
 * @method TeamType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TeamType::class);
    }

    // /**
    //  * @return TeamType[] Returns an array of TeamType objects
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
    public function findOneBySomeField($value): ?TeamType
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
