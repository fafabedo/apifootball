<?php

namespace App\Service\Processor\Order;

use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonMatchTeam;
use App\Entity\CompetitionSeasonTableItem;
use Doctrine\Common\Persistence\ManagerRegistry;

class OrderStrategyPoints implements OrderStrategyInterface
{
    private $doctrine;

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
        $competitionSeason = $tableItem1
            ->getCompetitionSeasonTable()
            ->setCompetitionSeason();

        $matches = $this->getDoctrine()
            ->getRepository(CompetitionSeasonMatch::class)
            ->findBy(['competition_season' => $competitionSeason]);

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
