<?php

namespace App\Repository;

use App\Entity\PlayerTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlayerTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerTeam[]    findAll()
 * @method PlayerTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerTeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PlayerTeam::class);
    }

    // /**
    //  * @return PlayerTeam[] Returns an array of PlayerTeam objects
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
    public function findOneBySomeField($value): ?PlayerTeam
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
