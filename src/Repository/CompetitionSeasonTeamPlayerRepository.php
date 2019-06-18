<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonTeam;
use App\Entity\CompetitionSeasonTeamPlayer;
use App\Entity\Player;
use App\Entity\Team;
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

    public function findOneByPlayerAndTeam(CompetitionSeasonTeam $competitionSeasonTeam, Player $player)
    {
        $player_id = -1;
        if (!is_numeric($player->getId())) {
            $player_id = $player->getId();
        }
        return $this->createQueryBuilder('cstp')
            ->innerJoin('cstp.player', 'p')
            ->where('cstp.competition_season_team = :season_team AND p.id = :player_id')
            ->setParameter('season_team', $competitionSeasonTeam)
            ->setParameter('player_id', $player_id)
            ->getQuery()
            ->getResult();

    }

    /**
     * @param Competition $competition
     * @param bool $archive
     * @return mixed
     * @throws \Exception
     */
    public function findByCompetition(Competition $competition, $archive = false)
    {
        return $this->createQueryBuilder('cstp')
            ->innerJoin('cstp.player', 'p')
            ->innerJoin('cstp.competition_season_team', 'cst')
            ->innerJoin('cst.competition_season', 'cs')
            ->where('cs.competition = :competition')
            ->andWhere('cs.archive = :archive')
            ->setParameter('competition', $competition)
            ->setParameter('archive', $archive)
            ->orderBy('cstp.id', 'asc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return mixed
     * @throws \Exception
     */
    public function findByCompetitionSeason(CompetitionSeason $competitionSeason)
    {
        return $this->createQueryBuilder('cstp')
            ->innerJoin('cstp.player', 'p')
            ->innerJoin('cstp.competition_season_team', 'cst')
            ->innerJoin('cst.competition_season', 'cs')
            ->where('cst.competition_season = :competition_season')
            ->setParameter('competition_season', $competitionSeason)
            ->orderBy('cstp.id', 'asc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Team $team
     * @param bool $archive
     * @return mixed
     * @throws \Exception
     */
    public function findByTeam(Team $team, $archive = false)
    {
        return $this->createQueryBuilder('cstp')
            ->innerJoin('cstp.player', 'p')
            ->innerJoin('cstp.competition_season_team', 'cst')
            ->innerJoin('cst.competition_season', 'cs')
            ->where('cst.team = :team AND cs.archive = :archive')
            ->setParameter('team', $team)
            ->setParameter('archive', $archive)
            ->orderBy('cstp.id', 'asc')
            ->getQuery()
            ->getResult();
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
