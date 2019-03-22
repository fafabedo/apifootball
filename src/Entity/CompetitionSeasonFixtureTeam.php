<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonFixtureTeamRepository")
 */
class CompetitionSeasonFixtureTeam
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="competitionSeasonFixtureTeams")
     */
    private $team;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_home;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $final_score;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_victory;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeasonFixture", inversedBy="competitionSeasonFixtureTeams")
     */
    private $competition_season_fixture;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     * @return CompetitionSeasonFixtureTeam
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsHome(): ?bool
    {
        return $this->is_home;
    }

    /**
     * @param bool $is_home
     * @return CompetitionSeasonFixtureTeam
     */
    public function setIsHome(bool $is_home): self
    {
        $this->is_home = $is_home;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFinalScore(): ?int
    {
        return $this->final_score;
    }

    /**
     * @param int|null $final_score
     * @return CompetitionSeasonFixtureTeam
     */
    public function setFinalScore(?int $final_score): self
    {
        $this->final_score = $final_score;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsVictory(): ?bool
    {
        return $this->is_victory;
    }

    /**
     * @param bool|null $is_victory
     * @return CompetitionSeasonFixtureTeam
     */
    public function setIsVictory(?bool $is_victory): self
    {
        $this->is_victory = $is_victory;

        return $this;
    }

    /**
     * @return CompetitionSeasonFixture|null
     */
    public function getCompetitionSeasonFixture(): ?CompetitionSeasonFixture
    {
        return $this->competition_season_fixture;
    }

    /**
     * @param CompetitionSeasonFixture|null $competition_season_fixture
     * @return CompetitionSeasonFixtureTeam
     */
    public function setCompetitionSeasonFixture(?CompetitionSeasonFixture $competition_season_fixture): self
    {
        $this->competition_season_fixture = $competition_season_fixture;

        return $this;
    }
}
