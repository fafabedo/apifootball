<?php

namespace App\Service\Processor\Table\Strategy;

use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonTable;
use App\Entity\CompetitionSeasonTeam;
use App\Service\Processor\Table\AbstractTableProcessor;
use App\Service\Processor\Table\TableProcessorInterface;

class TableGroupsProcessor extends AbstractTableProcessor implements TableProcessorInterface
{
    /**
     * @var CompetitionSeasonTable[]
     */
    private $groupTables = [];
    /**
     * @param CompetitionSeason $competitionSeason
     * @param $groupName
     * @param CompetitionSeasonTable|null $seasonTable
     * @return CompetitionSeasonTable
     * @throws \Exception
     */
    private function buildBaseTable(
        CompetitionSeason $competitionSeason,
        $groupName,
        CompetitionSeasonTable $seasonTable = null)
    {
        $seasonTeams = $this->getDoctrine()
            ->getRepository(CompetitionSeasonTeam::class)
            ->findBy([
                'competition_season' => $competitionSeason,
                'groupName' => $groupName
            ]);

        if (!$seasonTable instanceof CompetitionSeasonTable) {
            $seasonTable = new CompetitionSeasonTable();
            $seasonTable->setCompetitionSeason($competitionSeason);
        }
        $dateTimeTable = new \DateTime();
        $seasonTable->setTimestamp($dateTimeTable);
        $seasonTable->setGroupName($groupName);
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
     * @return $this
     * @throws \Exception
     */
    public function process()
    {
        $competitionSeason = $this->getCompetitionSeason();
        $groups = $this
            ->getDoctrine()
            ->getRepository(CompetitionSeasonMatch::class)
            ->findGroupsByCompetition($competitionSeason);

        foreach ($groups as $group) {
            $groupName = $group['MatchGroup'];
            $groupTable = $this->buildBaseTable($competitionSeason, $groupName);
            $matches = $this
                ->getDoctrine()
                ->getRepository(CompetitionSeasonMatch::class)
                ->findMatchesByGroup($competitionSeason, $groupName);

            $groupTable = $this->updateTableItemsByMatches($groupTable, $matches);
            $this->groupTables[] = $groupTable;
        }
        return $this;
    }

    /**
     * @return CompetitionSeasonTable[]
     */
    public function getTable()
    {
        return $this->groupTables;
    }

}
