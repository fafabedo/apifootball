<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\TeamAwardRepository")
 */
class TeamAward
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="teamAwards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Award", inversedBy="teamAwards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $award;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeason")
     * @ORM\JoinColumn(nullable=false)
     */
    private $competitionSeason;

  /**
   * @return int|null
   */
  public function getId(): ?int
    {
        return $this->id;
    }

  /**
   * @return \App\Entity\Team|null
   */
  public function getTeam(): ?Team
    {
        return $this->team;
    }

  /**
   * @param \App\Entity\Team|null $team
   *
   * @return \App\Entity\TeamAward
   */
  public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

  /**
   * @return \App\Entity\Award|null
   */
  public function getAward(): ?Award
    {
        return $this->award;
    }

  /**
   * @param \App\Entity\Award|null $award
   *
   * @return \App\Entity\TeamAward
   */
  public function setAward(?Award $award): self
    {
        $this->award = $award;

        return $this;
    }

  /**
   * @return \App\Entity\CompetitionSeason|null
   */
  public function getCompetitionSeason(): ?CompetitionSeason
    {
        return $this->competitionSeason;
    }

  /**
   * @param \App\Entity\CompetitionSeason|null $competitionSeason
   *
   * @return \App\Entity\TeamAward
   */
  public function setCompetitionSeason(?CompetitionSeason $competitionSeason): self
    {
        $this->competitionSeason = $competitionSeason;

        return $this;
    }
}
