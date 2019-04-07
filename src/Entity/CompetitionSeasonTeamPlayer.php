<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonTeamPlayerRepository")
 */
class CompetitionSeasonTeamPlayer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeasonTeam", inversedBy="competitionSeasonTeamPlayers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $competition_season_team;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CompetitionSeasonTeam|null
     */
    public function getCompetitionSeasonTeam(): ?CompetitionSeasonTeam
    {
        return $this->competition_season_team;
    }

    /**
     * @param CompetitionSeasonTeam|null $competition_season_team
     * @return CompetitionSeasonTeamPlayer
     */
    public function setCompetitionSeasonTeam(?CompetitionSeasonTeam $competition_season_team): self
    {
        $this->competition_season_team = $competition_season_team;

        return $this;
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @param Player|null $player
     * @return CompetitionSeasonTeamPlayer
     */
    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }
}
