<?php

namespace App\Service\Formula\Strategy;

use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonTable;
use App\Entity\CompetitionSeasonTableItem;
use App\Service\Formula\AbstractFormula;
use App\Service\Formula\FormulaInterface;

/**
 * Class GroupSortBestFormula
 * @package App\Service\Formula\Strategy
 */
class GroupSortBestFormula extends AbstractFormula implements FormulaInterface
{
    const FORMULA_REGEX = 'groups_best{([0-9]+)}~scope{([^}]+)}';

    /**
     * @var int
     */
    private $position;

    /**
     * @var array
     */
    private $groupScope = [];

    /**
     * @return $this
     */
    private function processParameters()
    {
        $formula = $this->getFormula();
        if (!$this->validateFormula($formula)) {
            return $this;
        }
        if (preg_match(self::FORMULA_REGEX, $formula, $matches)) {
            $this->position = $matches[1];
            $groups = $matches[2];
            $this->groupScope = explode(',', $groups);
        }
        return $this;

    }

    /**
     * @param $formula
     * @return bool
     */
    public function validateFormula($formula)
    {
        if (preg_match(self::FORMULA_REGEX, $formula)) {
            return true;
        }
        return false;
    }

    /**
     * @return null
     */
    public function getTeam()
    {
        $this->processParameters();
        $competitionSeason = $this->getCompetitionSeason();
        if (!$competitionSeason instanceof CompetitionSeason) {
            return null;
        }
        $groups = $this->getDoctrine()
            ->getRepository(CompetitionSeasonTable::class)
            ->findGroupsByCompetition($competitionSeason);

        $seasonTables = $this->getDoctrine()
            ->getRepository(CompetitionSeasonTable::class)
            ->findBy(['competitionSeason' => $competitionSeason]);

        $position = $this->position;
        $items = [];
        foreach ($seasonTables as $seasonTable) {
            $items[] = $seasonTable->getCompetitionSeasonTableItems()->filter(
                function(CompetitionSeasonTableItem $tableItem) use($position) {
                    return ($tableItem->getPosition() === $position);
                })->first();
        }

    }

}
