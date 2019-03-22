<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonFixtureRepository")
 */
class CompetitionSeasonFixture
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $match_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $match_time;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $attendance;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeason", inversedBy="competitionSeasonFixtures")
     */
    private $competition_season;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonFixtureTeam", mappedBy="competition_season_fixture")
     */
    private $competitionSeasonFixtureTeams;

    public function __construct()
    {
        $this->competitionSeasonFixtureTeams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatchDate(): ?\DateTimeInterface
    {
        return $this->match_date;
    }

    public function setMatchDate(\DateTimeInterface $match_date): self
    {
        $this->match_date = $match_date;

        return $this;
    }

    public function getMatchTime(): ?\DateTimeInterface
    {
        return $this->match_time;
    }

    public function setMatchTime(?\DateTimeInterface $match_time): self
    {
        $this->match_time = $match_time;

        return $this;
    }

    public function getAttendance(): ?int
    {
        return $this->attendance;
    }

    public function setAttendance(?int $attendance): self
    {
        $this->attendance = $attendance;

        return $this;
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

    /**
     * @return Collection|CompetitionSeasonFixtureTeam[]
     */
    public function getCompetitionSeasonFixtureTeams(): Collection
    {
        return $this->competitionSeasonFixtureTeams;
    }

    public function addCompetitionSeasonFixtureTeam(CompetitionSeasonFixtureTeam $competitionSeasonFixtureTeam): self
    {
        if (!$this->competitionSeasonFixtureTeams->contains($competitionSeasonFixtureTeam)) {
            $this->competitionSeasonFixtureTeams[] = $competitionSeasonFixtureTeam;
            $competitionSeasonFixtureTeam->setCompetitionSeasonFixture($this);
        }

        return $this;
    }

    public function removeCompetitionSeasonFixtureTeam(CompetitionSeasonFixtureTeam $competitionSeasonFixtureTeam): self
    {
        if ($this->competitionSeasonFixtureTeams->contains($competitionSeasonFixtureTeam)) {
            $this->competitionSeasonFixtureTeams->removeElement($competitionSeasonFixtureTeam);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonFixtureTeam->getCompetitionSeasonFixture() === $this) {
                $competitionSeasonFixtureTeam->setCompetitionSeasonFixture(null);
            }
        }

        return $this;
    }
}
