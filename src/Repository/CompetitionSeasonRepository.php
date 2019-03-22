<?php

namespace App\Repository;

use App\Entity\CompetitionSeason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeason|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeason|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeason[]    findAll()
 * @method CompetitionSeason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeason::class);
    }

    // /**
    //  * @return CompetitionSeason[] Returns an array of CompetitionSeason objects
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
    public function findOneBySomeField($value): ?CompetitionSeason
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
