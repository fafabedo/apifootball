<?php

namespace App\Repository;

use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonTable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeasonTable|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeasonTable|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeasonTable[]    findAll()
 * @method CompetitionSeasonTable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonTableRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeasonTable::class);
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @param $matchDay
     * @return CompetitionSeasonTable|null
     */
    public function findOneTableBySeasonAndMatchDay(CompetitionSeason $competitionSeason, $matchDay)
    {
        return $this
            ->findOneBy(
                [
                    'competitionSeason' => $competitionSeason,
                    'match_day' => $matchDay
                ]
            );
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @param $groupName
     * @return CompetitionSeasonTable|null
     */
    public function findOneTableByGroupName(CompetitionSeason $competitionSeason, $groupName)
    {
        return $this->findOneBy(
            [
                'competitionSeason' => $competitionSeason,
                'groupName' => $groupName
            ]
        );

    }

    public function findGroupsByCompetition(CompetitionSeason $competitionSeason)
    {
        return $this->createquerybuilder('cst')
            ->select('cst.MatchGroup')
            ->where('csm.competition_season = :competition')
            ->setParameter('competition', $competitionSeason)
            ->orderby('csm.MatchGroup', 'asc')
            ->distinct()
            ->getquery()
            ->getresult();
    }

    // /**
    //  * @return CompetitionSeasonTable[] Returns an array of CompetitionSeasonTable objects
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
    public function findOneBySomeField($value): ?CompetitionSeasonTable
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
