<?php

namespace App\Service\Processor\Competition;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonMatchTeam;
use App\Entity\CompetitionSeasonTable;
use App\Entity\CompetitionSeasonTableItem;
use App\Entity\CompetitionSeasonTeam;
use App\Entity\CompetitionType;
use App\Entity\Team;
use App\Service\Processor\AbstractProcessor;
use App\Service\Processor\Order\OrderProxy;
use App\Service\Processor\Table\TableProxy;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * Class CompetitionProcessor
 * @package App\Service\Processor\Competition
 */
class CompetitionProcessor extends AbstractProcessor
{
    /**
     * @var Competition[]
     */
    private $competitions;

    /**
     * @var CompetitionSeasonTable[]
     */
    private $tables = [];

    /**
     * @var array
     */
    private $matchDay = [];

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var StyleInterface
     */
    private $outputStyle;

    /**
     * @var OrderProxy
     */
    private $orderProxy;

    /**
     * @var TableProxy
     */
    private $tableProxy;

    /**
     * TableProcessor constructor.
     * @param ManagerRegistry $doctrine
     * @param TableProxy $tableProxy
     * @param OrderProxy $orderProxy
     * @throws \Exception
     */
    public function __construct(ManagerRegistry $doctrine, TableProxy $tableProxy, OrderProxy $orderProxy)
    {
        $this->doctrine = $doctrine;
        $this->tableProxy = $tableProxy;
        $this->orderProxy = $orderProxy;
        parent::__construct($doctrine);
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @return Competition[]
     */
    public function getCompetitions(): array
    {
        return $this->competitions;
    }

    /**
     * @param Competition[] $competitions
     * @return CompetitionProcessor
     */
    public function setCompetitions(array $competitions): CompetitionProcessor
    {
        $this->competitions = $competitions;
        return $this;
    }

    /**
     * @return array
     */
    public function getMatchDay(): array
    {
        return $this->matchDay;
    }

    /**
     * @param array $matchDay
     * @return CompetitionProcessor
     */
    public function setMatchDay(array $matchDay): CompetitionProcessor
    {
        $this->matchDay = $matchDay;
        return $this;
    }

    /**
     * @return StyleInterface
     */
    public function getOutputStyle(): StyleInterface
    {
        return $this->outputStyle;
    }

    /**
     * @param StyleInterface $outputStyle
     * @return CompetitionProcessor
     */
    public function setOutputStyle(StyleInterface $outputStyle): CompetitionProcessor
    {
        $this->outputStyle = $outputStyle;
        return $this;
    }

    /**
     * @return TableProxy
     */
    public function getTableProxy(): TableProxy
    {
        return $this->tableProxy;
    }

    /**
     * @return OrderProxy
     */
    public function getOrderProxy(): OrderProxy
    {
        return $this->orderProxy;
    }

    /**
     * @return CompetitionProcessor
     * @throws \Exception
     */
    public function process(): CompetitionProcessor
    {
        $seasons = $this->getCompetitionSeasons();
        foreach ($seasons as $competitionSeason) {
            $competitionTypeId = $competitionSeason
                ->getCompetition()
                ->getCompetitionType()
                ->getId();
            switch ($competitionTypeId) {
                case CompetitionType::TOURNAMENT:
                    $this
                        ->getTableProxy()
                        ->setProcessorByClass('TableGroupsProcessor');
                    ;
                    break;
                case CompetitionType::LEAGUE:
                default:
                    $this
                        ->getTableProxy()
                        ->setProcessorByClass('TableLeagueProcessor')
                        ->setParameter('match_days', $this->getMatchDay());
                    break;
            }
            $tables = $this
                ->getTableProxy()
                ->setCompetitionSeason($competitionSeason)
                ->process()
                ->getData();

            /* @ Order Table position */
            $tables = $this
                ->getOrderProxy()
                ->setStrategyByClass('OrderPointsThenDirectMatches')
                ->setCompetitionSeasonTables($tables)
                ->process()
                ->getData()
            ;

            $this->tables = array_merge($this->tables, $tables);
        }
        return $this;
    }

    /**
     * @return CompetitionSeasonTable[]
     */
    public function getData()
    {
        return $this->tables;
    }

    /**
     * @return CompetitionProcessor
     */
    public function saveData(): CompetitionProcessor
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($this->tables as $table) {
            $em->persist($table);
        }
        $em->flush();
        return $this;
    }

    /**
     * @param CompetitionSeasonTable $table
     * @param CompetitionSeasonTeam[] $baseTeams
     * @return CompetitionSeasonTable
     */
    private function orderTableWithBaseTeams(CompetitionSeasonTable $table, array $baseTeams)
    {
        /* Include all teams */
        foreach ($baseTeams as $competitionSeasonTeam) {
            $teamFound = $table->getCompetitionSeasonTableItems()->filter(function (
                CompetitionSeasonTableItem $tableItem
            )
            use ($competitionSeasonTeam) {
                return ($tableItem->getTeam()->getId() === $competitionSeasonTeam->getTeam()->getId());
            });
            if ($teamFound->isEmpty()) {
                $tableItem = $this->createNewEmptyTableItem($table, $competitionSeasonTeam->getTeam());
                $table->addCompetitionSeasonTableItem($tableItem);
            }
        }

        /* Update position */
        foreach ($table->getCompetitionSeasonTableItems() as $tableItem) {
            $items[$tableItem->getTeam()->getId()] = $tableItem->getPoints();
        }
        $items = $table->getCompetitionSeasonTableItems()->toArray();
        usort($items, [$this, "sortTeamsByCriteria"]);

        $position = 1;
        foreach ($items as $item) {
            $tableItem = $table
                ->getCompetitionSeasonTableItems()
                ->filter(function (CompetitionSeasonTableItem $tableItem) use ($item) {
                    return ($tableItem->getTeam()->getId() === $item->getTeam()->getId());
                })->first();
            $tableItem->setPosition($position);
            $position++;
        }
        return $table;
    }

    /**
     * @param CompetitionSeasonTableItem $tableItem
     * @param CompetitionSeasonMatch $match
     * @param bool $isHome
     * @return CompetitionSeasonTableItem
     */
    private function processTableItemFromMatch(
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
    private function createNewEmptyTableItem(CompetitionSeasonTable $table, Team $team)
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


    /**
     * @param CompetitionSeasonMatch[] $matches
     * @return array
     */
    private function getStartsMatchDayDateTime(array $matches)
    {
        $startsAt = [];
        /* @var $match CompetitionSeasonMatch */
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
        return $startsAt;
    }

    /**
     * @return CompetitionSeason[]|object[]
     */
    private function getCompetitionSeasons()
    {
        switch (true) {
            case (!empty($this->getCompetitions())):
                return $this
                    ->getDoctrine()
                    ->getRepository(CompetitionSeason::class)
                    ->findBy(['competition' => $this->getCompetitions(), 'archive' => false]);
                break;
            default:
                return $this
                    ->getDoctrine()
                    ->getRepository(CompetitionSeason::class)
                    ->findBy(['archive' => false]);
                break;
        }
    }

    /**
     * @param CompetitionSeasonTableItem $a
     * @param CompetitionSeasonTableItem $b
     * @return int
     */
    private function sortTeamsByCriteria($a, $b)
    {
        switch (true) {
            case ($a->getPoints() < $b->getPoints()):
                return -1;
                break;
            case ($a->getPoints() === $b->getPoints()):
                $aDiff = $a->getGoalsFor() - $a->getGoalsAgainst();
                $bDiff = $b->getGoalsFor() - $b->getGoalsAgainst();
                if ($aDiff === $bDiff) {
                    return ($a->getGoalsFor() < $b->getGoalsFor() ? -1 : 1);
                }
                else {
                    return ($aDiff < $bDiff ? -1 : 1);
                }
                break;
            default:
                return 1;
                break;
        }

    }

}
