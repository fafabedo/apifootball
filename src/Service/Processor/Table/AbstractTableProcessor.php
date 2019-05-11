<?php

namespace App\Service\Processor\Table;

use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonMatchTeam;
use App\Entity\CompetitionSeasonTable;
use App\Entity\CompetitionSeasonTableItem;
use App\Entity\Team;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ManagerRegistry;

abstract class AbstractTableProcessor implements TableProcessorInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionSeason
     */
    private $competitionSeason;

    /**
     * TableStandardProcessor constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @return CompetitionSeason
     */
    public function getCompetitionSeason(): ?CompetitionSeason
    {
        return $this->competitionSeason;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setParameter($name, $value)
    {
        $method = 'set' . Inflector::camelize($name);
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return $this
     */
    public function setCompetitionSeason(CompetitionSeason $competitionSeason): self
    {
        $this->competitionSeason = $competitionSeason;
        return $this;
    }

    /**
     * @param CompetitionSeasonTable $seasonTable
     * @param CompetitionSeasonMatch[] $matches
     * @return CompetitionSeasonTable
     */
    protected function updateTableItemsByMatches(CompetitionSeasonTable $seasonTable, $matches): CompetitionSeasonTable
    {
        foreach ($matches as $match) {
            foreach ([true, false] as $isHome) {
                $matchTeam = $match->getCompetitionSeasonMatchTeams()->filter(function (
                    CompetitionSeasonMatchTeam $matchTeam
                ) use ($isHome) {
                    return ($matchTeam->getIsHome() === $isHome);
                })->first();
                if (!$matchTeam instanceof CompetitionSeasonMatchTeam) {
                    continue;
                }
                $team = $matchTeam->getTeam();
                $tableItem = $seasonTable
                    ->getCompetitionSeasonTableItems()
                    ->filter(function (CompetitionSeasonTableItem $tableItem) use ($team) {
                        return ($tableItem->getTeam()->getId() === $team->getId());
                    })->first();

                $this->processTableItemFromMatch($tableItem, $match, $isHome);
            }
        }
        return $seasonTable;
    }

    /**
     * @param CompetitionSeasonTableItem $tableItem
     * @param CompetitionSeasonMatch $match
     * @param bool $isHome
     * @return CompetitionSeasonTableItem
     */
    protected function processTableItemFromMatch(
        CompetitionSeasonTableItem $tableItem,
        CompetitionSeasonMatch $match,
        $isHome = true
    ): CompetitionSeasonTableItem {
        $matchTeam = $match
            ->getCompetitionSeasonMatchTeams()
            ->filter(function (CompetitionSeasonMatchTeam $matchTeam) use ($isHome) {
                return ($matchTeam->getIsHome() === $isHome);
            })->first();

        $matchTeamAdversary = $match
            ->getCompetitionSeasonMatchTeams()
            ->filter(function (CompetitionSeasonMatchTeam $matchTeam) use ($isHome) {
                return ($matchTeam->getIsHome() === !$isHome);
            })->first();


        /* @var CompetitionSeasonMatchTeam $matchTeam */
        $tableItem->setTeam($matchTeam->getTeam());
        /* Match counting */
        $tableItem->setMatches(($tableItem->getMatches() + 1));
        $win = $matchTeam->getIsVictory() ? 1 : 0;
        $draw = $matchTeam->getIsDraw() ? 1 : 0;
        $lose = $matchTeam->getIsVictory() ? 0 : 1;
        $tableItem->setMatchesWin($win ? ($tableItem->getMatchesWin() + 1) : $tableItem->getMatchesWin());
        $tableItem->setMatchesDraw($draw ? ($tableItem->getMatchesDraw() + 1) : $tableItem->getMatchesDraw());
        $tableItem->setMatchesLost($lose ? ($tableItem->getMatchesLost() + 1) : $tableItem->getMatchesLost());
        switch (true) {
            case $isHome:
                $tableItem->setHome($matchTeam->getIsHome() ? ($tableItem->getHome() + 1) : $tableItem->getHome());
                $tableItem->setHomeWin(($matchTeam->getIsHome() && $win) ? ($tableItem->getHomeWin() + 1) : $tableItem->getHomeWin());
                $tableItem->setHomeDraw(($matchTeam->getIsHome() && $draw) ? ($tableItem->getHomeDraw() + 1) : $tableItem->getHomeDraw());
                $tableItem->setHome(($matchTeam->getIsHome() && $lose) ? ($tableItem->getHomeLost() + 1) : $tableItem->getHomeLost());
                break;
            default:
                $tableItem->setAway(!$matchTeam->getIsHome() ?? ($tableItem->getAway() + 1));
                $tableItem->setAwayWin((!$matchTeam->getIsHome() && $win) ? ($tableItem->getAwayWin() + 1) : $tableItem->getAwayWin());
                $tableItem->setAwayDraw((!$matchTeam->getIsHome() && $draw) ? ($tableItem->getAwayDraw() + 1) : $tableItem->getAwayDraw());
                $tableItem->setAwayLost((!$matchTeam->getIsHome() && $lose) ? ($tableItem->getAwayLost() + 1) : $tableItem->getAwayLost());
                break;
        }
        $tableItem->setGoalsFor($tableItem->getGoalsFor() + $matchTeam->getScore());
        $tableItem->setGoalsAgainst($tableItem->getGoalsAgainst() + $matchTeamAdversary->getScore());

        switch (true) {
            case $win === 1:
                $tableItem->setPoints($tableItem->getPoints() + 3);
                break;
            case $draw === 1:
                $tableItem->setPoints($tableItem->getPoints() + 1);
                break;
            case $lose === 1:
                $tableItem->setPoints($tableItem->getPoints() + 0);
                break;
        }

        return $tableItem;
    }

    /**
     * @param CompetitionSeasonTable $table
     * @param Team $team
     * @return CompetitionSeasonTableItem
     */
    protected function createEmptyTableItem(CompetitionSeasonTable $table, Team $team)
    {
        $tableItem = new CompetitionSeasonTableItem();
        $tableItem->setCompetitionSeasonTable($table);
        $tableItem->setTeam($team);
        $tableItem->setPoints(0);
        $tableItem->setHome(0);
        $tableItem->setHomeWin(0);
        $tableItem->setHomeDraw(0);
        $tableItem->setHomeLost(0);
        $tableItem->setAway(0);
        $tableItem->setAwayWin(0);
        $tableItem->setAwayDraw(0);
        $tableItem->setAwayLost(0);
        $tableItem->setGoalsAgainst(0);
        $tableItem->setGoalsFor(0);
        $tableItem->setMatches(0);
        $tableItem->setMatchesWin(0);
        $tableItem->setMatchesDraw(0);
        $tableItem->setMatchesLost(0);
        $tableItem->setPosition(0);
        return $tableItem;
    }

}
