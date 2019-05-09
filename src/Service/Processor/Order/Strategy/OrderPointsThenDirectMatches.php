<?php

namespace App\Service\Processor\Order\Strategy;

use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonMatchTeam;
use App\Entity\CompetitionSeasonTableItem;
use App\Service\Processor\Order\OrderStrategyInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class OrderStrategyPoints
 * @package App\Service\Processor\Order\Strategy
 */
class OrderPointsThenDirectMatches implements OrderStrategyInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionSeasonMatch[]
     */
    private $matches;

    /**
     * OrderStrategyPoints constructor.
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
     * @param $matches
     * @return OrderPointsThenDirectMatches
     */
    public function setMatches($matches)
    {
        $this->matches = $matches;
        return $this;
    }

    /**
     * @return CompetitionSeasonMatch[]
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @param CompetitionSeasonTableItem $tableItem1
     * @param CompetitionSeasonTableItem $tableItem2
     * @return int
     */
    public function sortItems($tableItem1, $tableItem2)
    {
        switch (true) {
            case ($tableItem1->getPoints() < $tableItem2->getPoints()):
                return 1;
                break;
            case ($tableItem1->getPoints() === $tableItem2->getPoints()):
                $aDiff = $tableItem1->getGoalsFor() - $tableItem1->getGoalsAgainst();
                $bDiff = $tableItem2->getGoalsFor() - $tableItem2->getGoalsAgainst();
                if ($aDiff === $bDiff) {
                    return $this->sortByMatchBetweenThem($tableItem1, $tableItem2);
                }
                else {
                    return ($aDiff < $bDiff ? 1 : -1);
                }
                break;
            default:
                return -1;
                break;
        }
    }

    /**
     * @param CompetitionSeasonTableItem $tableItem1
     * @param CompetitionSeasonTableItem $tableItem2
     * @return int
     */
    private function sortByMatchBetweenThem($tableItem1, $tableItem2)
    {
        $matches = $this->getMatches();
        $t1win = 0;
        $t2win = 0;
        foreach ($matches as $match) {
            $matchTeam1 = $match->getCompetitionSeasonMatchTeams()->filter(function(CompetitionSeasonMatchTeam $matchTeam) use($tableItem1) {
                return ($matchTeam->getTeam() === $tableItem1->getTeam());
            });
            $matchTeam2 = $match->getCompetitionSeasonMatchTeams()->filter(function(CompetitionSeasonMatchTeam $matchTeam) use($tableItem2) {
                return ($matchTeam->getTeam() === $tableItem2->getTeam());
            });
            if ($matchTeam1->count() > 0 && $matchTeam2->count() > 0) {
                /* @var CompetitionSeasonMatchTeam $mt1 */
                $mt1 = $matchTeam1->first();
                $t1win += (int) $mt1->getIsVictory();

                /* @var CompetitionSeasonMatchTeam $mt */
                $mt2 = $matchTeam1->first();
                $t2win += (int) $mt2->getIsVictory();
            }
        }

        return ($t1win > $t2win ? 1 : -1);

    }

}
