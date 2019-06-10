<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"table"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonTableItemRepository")
 */
class CompetitionSeasonTableItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     * @Groups({"season"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeasonTable", inversedBy="competitionSeasonTableItems")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competition_season_table;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $matches;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $matches_won;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $matches_drawn;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $matches_lost;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $home;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $home_won;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $home_drawn;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $home_lost;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $away;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $away_won;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $away_drawn;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $away_lost;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $goals_for;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $goals_against;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $points;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CompetitionSeasonTable|null
     * @Groups({"table"})
     */
    public function getCompetitionSeasonTable(): ?CompetitionSeasonTable
    {
        return $this->competition_season_table;
    }

    /**
     * @param CompetitionSeasonTable|null $competition_season_table
     * @return CompetitionSeasonTableItem
     */
    public function setCompetitionSeasonTable(?CompetitionSeasonTable $competition_season_table): self
    {
        $this->competition_season_table = $competition_season_table;

        return $this;
    }

    /**
     * @return Team|null
     * @Groups({"table"})
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     * @return CompetitionSeasonTableItem
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getMatches(): ?int
    {
        return $this->matches;
    }

    /**
     * @param int|null $matches
     * @return CompetitionSeasonTableItem
     */
    public function setMatches(?int $matches): self
    {
        $this->matches = $matches;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getMatchesWon(): ?int
    {
        return $this->matches_won;
    }

    /**
     * @param int|null $matches_won
     * @return CompetitionSeasonTableItem
     */
    public function setMatchesWon(?int $matches_won): self
    {
        $this->matches_won = $matches_won;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getMatchesDrawn(): ?int
    {
        return $this->matches_drawn;
    }

    /**
     * @param int|null $matches_drawn
     * @return CompetitionSeasonTableItem
     */
    public function setMatchesDrawn(?int $matches_drawn): self
    {
        $this->matches_drawn = $matches_drawn;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getMatchesLost(): ?int
    {
        return $this->matches_lost;
    }

    /**
     * @param int|null $matches_lost
     * @return CompetitionSeasonTableItem
     */
    public function setMatchesLost(?int $matches_lost): self
    {
        $this->matches_lost = $matches_lost;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getHome(): ?int
    {
        return $this->home;
    }

    /**
     * @param int|null $home
     * @return CompetitionSeasonTableItem
     */
    public function setHome(?int $home): self
    {
        $this->home = $home;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getHomeWon(): ?int
    {
        return $this->home_won;
    }

    /**
     * @param int|null $home_won
     * @return CompetitionSeasonTableItem
     */
    public function setHomeWon(?int $home_won): self
    {
        $this->home_won = $home_won;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getHomeDrawn(): ?int
    {
        return $this->home_drawn;
    }

    /**
     * @param int|null $home_drawn
     * @return CompetitionSeasonTableItem
     */
    public function setHomeDrawn(?int $home_drawn): self
    {
        $this->home_drawn = $home_drawn;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getHomeLost(): ?int
    {
        return $this->home_lost;
    }

    /**
     * @param int|null $home_lost
     * @return CompetitionSeasonTableItem
     */
    public function setHomeLost(?int $home_lost): self
    {
        $this->home_lost = $home_lost;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getAway(): ?int
    {
        return $this->away;
    }

    /**
     * @param int|null $away
     * @return CompetitionSeasonTableItem
     */
    public function setAway(?int $away): self
    {
        $this->away = $away;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getAwayWon(): ?int
    {
        return $this->away_won;
    }

    /**
     * @param int|null $away_won
     * @return CompetitionSeasonTableItem
     */
    public function setAwayWon(?int $away_won): self
    {
        $this->away_won = $away_won;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getAwayDrawn(): ?int
    {
        return $this->away_drawn;
    }

    /**
     * @param int|null $away_drawn
     * @return CompetitionSeasonTableItem
     */
    public function setAwayDrawn(?int $away_drawn): self
    {
        $this->away_drawn = $away_drawn;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getAwayLost(): ?int
    {
        return $this->away_lost;
    }

    /**
     * @param int|null $away_lost
     * @return CompetitionSeasonTableItem
     */
    public function setAwayLost(?int $away_lost): self
    {
        $this->away_lost = $away_lost;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getGoalsFor(): ?int
    {
        return $this->goals_for;
    }

    /**
     * @param int|null $goals_for
     * @return CompetitionSeasonTableItem
     */
    public function setGoalsFor(?int $goals_for): self
    {
        $this->goals_for = $goals_for;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getGoalsAgainst(): ?int
    {
        return $this->goals_against;
    }

    /**
     * @param int|null $goals_against
     * @return CompetitionSeasonTableItem
     */
    public function setGoalsAgainst(?int $goals_against): self
    {
        $this->goals_against = $goals_against;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getPoints(): ?int
    {
        return $this->points;
    }

    /**
     * @param int|null $points
     * @return CompetitionSeasonTableItem
     */
    public function setPoints(?int $points): self
    {
        $this->points = $points;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     * @return CompetitionSeasonTableItem
     */
    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
