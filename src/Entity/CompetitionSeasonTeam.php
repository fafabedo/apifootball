<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonTeamRepository")
 */
class CompetitionSeasonTeam
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeason", inversedBy="competitionSeasonTeams")
     */
    private $competition_season;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="competitionSeasonTeams")
     */
    private $team;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetitionSeason(): ?CompetitionSeason
    {
        return $this->competition_season;
    }

    public function setCompetitionSeason(?CompetitionSeason $competition_season): self
    {
        $this->competition_season = $competition_season;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }
}
