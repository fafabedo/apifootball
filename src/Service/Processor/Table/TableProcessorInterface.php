<?php


namespace App\Service\Processor\Table;

use App\Entity\CompetitionSeason;

interface TableProcessorInterface
{
    public function setCompetitionSeason(CompetitionSeason $competitionSeason);

    public function getCompetitionSeason(): ?CompetitionSeason;

    public function setParameter($name, $value);

    public function process();

    public function getTable();

}
