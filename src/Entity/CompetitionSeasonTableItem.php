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
     * @ORM\Column(type="integer")
     * @Groups({"season"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeasonTable", inversedBy="competitionSeasonTableItems")
     * @ORM\JoinColumn(nullable=false)
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
    private $matches_win;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $matches_draw;

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
    private $home_win;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $home_draw;

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
    private $away_win;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $away_draw;

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
    public function getMatchesWin(): ?int
    {
        return $this->matches_win;
    }

    /**
     * @param int|null $matches_win
     * @return CompetitionSeasonTableItem
     */
    public function setMatchesWin(?int $matches_win): self
    {
        $this->matches_win = $matches_win;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getMatchesDraw(): ?int
    {
        return $this->matches_draw;
    }

    /**
     * @param int|null $matches_draw
     * @return CompetitionSeasonTableItem
     */
    public function setMatchesDraw(?int $matches_draw): self
    {
        $this->matches_draw = $matches_draw;

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
    public function getHomeWin(): ?int
    {
        return $this->home_win;
    }

    /**
     * @param int|null $home_win
     * @return CompetitionSeasonTableItem
     */
    public function setHomeWin(?int $home_win): self
    {
        $this->home_win = $home_win;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getHomeDraw(): ?int
    {
        return $this->home_draw;
    }

    /**
     * @param int|null $home_draw
     * @return CompetitionSeasonTableItem
     */
    public function setHomeDraw(?int $home_draw): self
    {
        $this->home_draw = $home_draw;

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
    public function getAwayWin(): ?int
    {
        return $this->away_win;
    }

    /**
     * @param int|null $away_win
     * @return CompetitionSeasonTableItem
     */
    public function setAwayWin(?int $away_win): self
    {
        $this->away_win = $away_win;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getAwayDraw(): ?int
    {
        return $this->away_draw;
    }

    /**
     * @param int|null $away_draw
     * @return CompetitionSeasonTableItem
     */
    public function setAwayDraw(?int $away_draw): self
    {
        $this->away_draw = $away_draw;

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
