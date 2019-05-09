<?php

namespace App\Service\Processor\Table\Strategy;

use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonMatchTeam;
use App\Entity\CompetitionSeasonTable;
use App\Entity\CompetitionSeasonTableItem;
use App\Entity\CompetitionSeasonTeam;
use App\Service\Processor\Table\AbstractTableProcessor;
use App\Service\Processor\Table\TableProcessorInterface;

class TableLeagueProcessor extends AbstractTableProcessor implements TableProcessorInterface
{
    /**
     * @var CompetitionSeasonTable[]
     */
    private $seasonTables = [];
    /**
     * @var array
     */
    private $matchDays = [];

    /**
     * @return array
     */
    public function getMatchDays(): array
    {
        return $this->matchDays;
    }

    /**
     * @param array $matchDays
     * @return TableLeagueProcessor
     */
    public function setMatchDays(array $matchDays): TableLeagueProcessor
    {
        $this->matchDays = $matchDays;
        return $this;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @param $matchDay
     * @param $timestamp
     * @param CompetitionSeasonTable|null $seasonTable
     * @return CompetitionSeasonTable
     */
    private function buildBaseTable(
        CompetitionSeason $competitionSeason,
        $matchDay,
        $timestamp,
        CompetitionSeasonTable $seasonTable = null): CompetitionSeasonTable
    {
        $seasonTeams = $this->getDoctrine()
            ->getRepository(CompetitionSeasonTeam::class)
            ->findBy(['competition_season' => $competitionSeason]);

        if (!$seasonTable instanceof CompetitionSeasonTable) {
            $seasonTable = new CompetitionSeasonTable();
            $seasonTable->setCompetitionSeason($competitionSeason);
        }
        $seasonTable->setMatchDay($matchDay);
        $seasonTable->setTimestamp($timestamp);
        $seasonTable
            ->getCompetitionSeasonTableItems()
            ->clear();

        foreach ($seasonTeams as $seasonTeam) {
            $tableItem = $this->createEmptyTableItem($seasonTable, $seasonTeam->getTeam());
            $seasonTable->addCompetitionSeasonTableItem($tableItem);
        }
        return $seasonTable;
    }


    /**
     * @return TableLeagueProcessor
     * @throws \Exception
     */
    public function process(): TableLeagueProcessor
    {
        $competitionSeason = $this->getCompetitionSeason();
        $allMatches = $this
            ->getDoctrine()
            ->getRepository(CompetitionSeasonMatch::class)
            ->findMatchesBySeason($competitionSeason);

        $startsAt = $this->getStartsMatchDayDateTime($allMatches);
        foreach ($startsAt as $matchDay => $dateTime) {
            if (!empty($this->getMatchDays()) && !in_array($matchDay ,$this->getMatchDays())) {
                continue;
            }
            $seasonTable = $this
                ->getDoctrine()
                ->getRepository(CompetitionSeasonTable::class)
                ->findOneTableBySeasonAndMatchDay($competitionSeason, $matchDay);

            $timestamp = $dateTime->getTimestamp() - (60 * 60 * 24);
            $dateTimeTable = (new \DateTime())->setTimestamp($timestamp);
            $seasonTable = $this->buildBaseTable($competitionSeason, $matchDay, $dateTimeTable, $seasonTable);

            $matches = $this
                ->getDoctrine()
                ->getRepository(CompetitionSeasonMatch::class)
                ->findMatchesByMathDay($competitionSeason, $matchDay, $startsAt[$matchDay + 1]);

            $seasonTable = $this->updateTableItemsByMatches($seasonTable, $matches);
            $this->seasonTables[] = $seasonTable;
        }
        return $this;
    }

    /**
     * @return CompetitionSeasonTable[]
     */
    public function getTable()
    {
        return $this->seasonTables;
    }

    /**
     * @param CompetitionSeasonMatch[] $matches
     * @return array
     * @throws \Exception
     */
    private function getStartsMatchDayDateTime(array $matches)
    {
        $startsAt = [];
        /* @var $match CompetitionSeasonMatch */
        $matchDay = 0;
        foreach ($matches as $match) {
            $matchDay = $match->getMatchDay();
            if (empty($startsAt[$matchDay])) {
                $startsAt[$matchDay] = $match->getMatchDatetime();
            }
            /* @var $date \DateTime */
            $date = $startsAt[$matchDay];
            if ($date->getTimestamp() > $match->getMatchDatetime()->getTimestamp()) {
                $startsAt[$matchDay] = $match->getMatchDatetime();
            }
        }
        $matchDay++;
        $startsAt[$matchDay] = new \DateTime();

        return $startsAt;
    }



}
