<?php

namespace App\Repository;

use App\Entity\CompetitionSeasonMatchTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeasonMatchTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeasonMatchTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeasonMatchTeam[]    findAll()
 * @method CompetitionSeasonMatchTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonMatchTeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeasonMatchTeam::class);
    }

    // /**
    //  * @return CompetitionSeasonMatchTeam[] Returns an array of CompetitionSeasonMatchTeam objects
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
    public function findOneBySomeField($value): ?CompetitionSeasonMatchTeam
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
