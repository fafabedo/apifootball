<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"fixture"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonMatchTeamRepository")
 */
class CompetitionSeasonMatchTeam
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeasonMatch", inversedBy="competitionSeasonMatchTeams")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competition_season_match;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=true)
     */
    private $team;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_home;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $score;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_victory;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_draw;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeasonMatch")
     */
    private $competition_season_match_result;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $formula;

    /**
     * @return int|null
     * @Groups({"fixture"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CompetitionSeasonMatch|null
     */
    public function getCompetitionSeasonMatch(): ?CompetitionSeasonMatch
    {
        return $this->competition_season_match;
    }

    /**
     * @param CompetitionSeasonMatch|null $competition_season_match
     * @return CompetitionSeasonMatchTeam
     */
    public function setCompetitionSeasonMatch(?CompetitionSeasonMatch $competition_season_match): self
    {
        $this->competition_season_match = $competition_season_match;

        return $this;
    }

    /**
     * @return Team|null
     * @Groups({"fixture"})
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     * @return CompetitionSeasonMatchTeam
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"fixture"})
     */
    public function getIsHome(): ?bool
    {
        return $this->is_home;
    }

    /**
     * @param bool|null $is_home
     * @return CompetitionSeasonMatchTeam
     */
    public function setIsHome(?bool $is_home): self
    {
        $this->is_home = $is_home;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"fixture"})
     */
    public function getScore(): ?int
    {
        return $this->score;
    }

    /**
     * @param int|null $score
     * @return CompetitionSeasonMatchTeam
     */
    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"fixture"})
     */
    public function getIsVictory(): ?bool
    {
        return $this->is_victory;
    }

    /**
     * @param bool|null $is_victory
     * @return CompetitionSeasonMatchTeam
     */
    public function setIsVictory(?bool $is_victory): self
    {
        $this->is_victory = $is_victory;

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"fixture"})
     */
    public function getIsDraw(): ?bool
    {
        return $this->is_draw;
    }

    /**
     * @param bool|null $is_draw
     * @return CompetitionSeasonMatchTeam
     */
    public function setIsDraw(?bool $is_draw): self
    {
        $this->is_draw = $is_draw;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"fixture"})
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     * @return CompetitionSeasonMatchTeam
     */
    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return CompetitionSeasonMatch|null
     */
    public function getCompetitionSeasonMatchResult(): ?CompetitionSeasonMatch
    {
        return $this->competition_season_match_result;
    }

    /**
     * @param CompetitionSeasonMatch|null $competition_season_match_result
     * @return CompetitionSeasonMatchTeam
     */
    public function setCompetitionSeasonMatchResult(?CompetitionSeasonMatch $competition_season_match_result): self
    {
        $this->competition_season_match_result = $competition_season_match_result;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"fixture", "season"})
     */
    public function getFormula(): ?string
    {
        return $this->formula;
    }

    /**
     * @param string|null $formula
     * @return CompetitionSeasonMatchTeam
     */
    public function setFormula(?string $formula): self
    {
        $this->formula = $formula;

        return $this;
    }
}
