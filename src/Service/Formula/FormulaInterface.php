<?php


namespace App\Service\Formula;


use App\Entity\CompetitionSeason;

interface FormulaInterface
{
    public function setCompetitionSeason(CompetitionSeason $competitionSeason);

    public function getCompetitionSeason();

    public function setParameter($name, $value);

    public function setFormula($formula);

    public function validateFormula($formula);

    public function getTeam();
}
