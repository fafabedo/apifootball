<?php

namespace App\Repository;

use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeasonMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeasonMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeasonMatch[]    findAll()
 * @method CompetitionSeasonMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonMatchRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeasonMatch::class);
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @param int $matchDay
     * @param \DateTime|null $dateLimit
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findMatchesByMathDay(CompetitionSeason $competitionSeason, $matchDay = 1, \DateTime $dateLimit = null)
    {
        $query = $this->createQueryBuilder('m')
            ->andWhere('m.competition_season = :season')
            ->setParameter('season', $competitionSeason)
            ->andWhere('m.match_day <= :md')
            ->setParameter('md', $matchDay)
        ;
        if ($dateLimit !== null) {
            $query
                ->andWhere('m.match_datetime <= :date_limit')
                ->setParameter('date_limit', $dateLimit)
            ;
        }
        return $query
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return CompetitionSeasonMatch[] Returns an array of CompetitionSeasonMatch objects
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
    public function findOneBySomeField($value): ?CompetitionSeasonMatch
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
