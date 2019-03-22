<?php

namespace App\Repository;

use App\Entity\CompetitionSeasonTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeasonTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeasonTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeasonTeam[]    findAll()
 * @method CompetitionSeasonTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonTeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeasonTeam::class);
    }

    // /**
    //  * @return CompetitionSeasonTeam[] Returns an array of CompetitionSeasonTeam objects
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
    public function findOneBySomeField($value): ?CompetitionSeasonTeam
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
