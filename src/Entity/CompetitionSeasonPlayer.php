<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonPlayerRepository")
 */
class CompetitionSeasonPlayer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="competitionSeasonPlayers")
     */
    private $competition_season_team;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetitionSeasonTeam(): ?Player
    {
        return $this->competition_season_team;
    }

    public function setCompetitionSeasonTeam(?Player $competition_season_team): self
    {
        $this->competition_season_team = $competition_season_team;

        return $this;
    }
}
