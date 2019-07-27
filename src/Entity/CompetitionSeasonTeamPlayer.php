<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"season_player", "player"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonTeamPlayerRepository")
 */
class CompetitionSeasonTeamPlayer
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeasonTeam", inversedBy="competitionSeasonTeamPlayers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competition_season_team;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @return int|null
     * @Groups({"season_player"})
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
     * @Groups({"season_player"})
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
