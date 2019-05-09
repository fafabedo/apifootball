<?php

namespace App\Repository;

use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\MatchStage;
use App\Traits\TmkEntityRepositoryTrait;
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
    use TmkEntityRepositoryTrait;

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

    /**
     * @param CompetitionSeason $competitionSeason
     * @param $groupName
     * @return CompetitionSeasonMatch[]
     */
    public function findMatchesByGroup(CompetitionSeason $competitionSeason, $groupName)
    {
        return $this->createquerybuilder('csm')
            ->where('csm.competition_season = :competition')
            ->andWhere('csm.MatchGroup = :group_name')
            ->andWhere('csm.isPlayed = :is_played')
            ->setParameter('competition', $competitionSeason)
            ->setParameter('group_name', $groupName)
            ->setParameter('is_played', 1)
            ->getquery()
            ->getresult();
    }

    /**
     * Retrieve group names for season matches
     * @param CompetitionSeason $competitionSeason
     * @return mixed
     */
    public function findGroupsByCompetition(CompetitionSeason $competitionSeason)
    {
        return $this->createquerybuilder('csm')
            ->select('csm.MatchGroup')
            ->innerJoin('csm.MatchStage', 'ms')
            ->where('csm.competition_season = :competition')
            ->setParameter('competition', $competitionSeason)
            ->andWhere('ms.name = :stage_name')
            ->setParameter('stage_name', MatchStage::MATCH_STAGE_GROUP)
            ->orderby('csm.MatchGroup', 'asc')
            ->distinct()
            ->getquery()
            ->getresult();
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @param bool $onlyPlayed
     * @return CompetitionSeasonMatch[]
     */
    public function findMatchesBySeason(CompetitionSeason $competitionSeason, $onlyPlayed = false)
    {
        $filters = [
            'competition_season' => $competitionSeason,
        ];
        if (!$onlyPlayed) {
            $filters['isPlayed'] = true;
        }
        return $this->findBy($filters);
    }

    // /**
    //  * @return CompetitionSeasonMatch[] Returns an array of CompetitionSeasonMatch objects
    //  */
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
