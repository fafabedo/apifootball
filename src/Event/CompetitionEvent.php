<?php


namespace App\Event;


use App\Entity\CompetitionSeason;

/**
 * Class CompetitionEvent
 * @package App\EventSubscriber
 */
class CompetitionEvent
{
    /**
     * Competition Season Match Update
     */
    const seasonUpdate = 'season.update';

    /**
     * @var CompetitionSeason
     */
    private $competitionSeason;

    /**
     * Events constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return CompetitionSeason
     */
    public function getCompetitionSeason(): CompetitionSeason
    {
        return $this->competitionSeason;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return CompetitionEvent
     */
    public function setCompetitionSeason(CompetitionSeason $competitionSeason): CompetitionEvent
    {
        $this->competitionSeason = $competitionSeason;
        return $this;
    }




}
