<?php

namespace App\Repository;

use App\Entity\CompetitionSeasonTeamPlayer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeasonTeamPlayer|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeasonTeamPlayer|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeasonTeamPlayer[]    findAll()
 * @method CompetitionSeasonTeamPlayer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonTeamPlayerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeasonTeamPlayer::class);
    }

    // /**
    //  * @return CompetitionSeasonTeamPlayer[] Returns an array of CompetitionSeasonTeamPlayer objects
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
    public function findOneBySomeField($value): ?CompetitionSeasonTeamPlayer
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
