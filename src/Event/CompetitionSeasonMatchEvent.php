<?php

namespace App\Event;

use App\Entity\CompetitionSeasonMatch;
use Symfony\Component\EventDispatcher\Event;

class CompetitionSeasonMatchEvent extends Event
{
    const POST_UPDATE = 'season.match.post_update';

    /**
     * @var CompetitionSeasonMatch[]
     */
    private $competitionMatches = [];

    /**
     * CompetitionSeasonMatchEvent constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return CompetitionSeasonMatch[]
     */
    public function getCompetitionMatches(): array
    {
        return $this->competitionMatches;
    }

    /**
     * @param CompetitionSeasonMatch[] $competitionMatches
     * @return CompetitionSeasonMatchEvent
     */
    public function setCompetitionMatches(array $competitionMatches): CompetitionSeasonMatchEvent
    {
        $this->competitionMatches = $competitionMatches;
        return $this;
    }




}
