<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Traits\MetadataTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonRepository")
 */
class CompetitionSeason
{
    use MetadataTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_season;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_season;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition", inversedBy="competitionSeasons")
     */
    private $competition;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonFixture", mappedBy="competition_season")
     */
    private $competitionSeasonFixtures;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonTeam", mappedBy="competition_season")
     */
    private $competitionSeasonTeams;

    public function __construct()
    {
        $this->competitionSeasonFixtures = new ArrayCollection();
        $this->competitionSeasonTeams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartSeason(): ?\DateTimeInterface
    {
        return $this->start_season;
    }

    public function setStartSeason(\DateTimeInterface $start_season): self
    {
        $this->start_season = $start_season;

        return $this;
    }

    public function getEndSeason(): ?\DateTimeInterface
    {
        return $this->end_season;
    }

    public function setEndSeason(\DateTimeInterface $end_season): self
    {
        $this->end_season = $end_season;

        return $this;
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): self
    {
        $this->competition = $competition;

        return $this;
    }

    public function getArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(?bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonFixture[]
     */
    public function getCompetitionSeasonFixtures(): Collection
    {
        return $this->competitionSeasonFixtures;
    }

    public function addCompetitionSeasonFixture(CompetitionSeasonFixture $competitionSeasonFixture): self
    {
        if (!$this->competitionSeasonFixtures->contains($competitionSeasonFixture)) {
            $this->competitionSeasonFixtures[] = $competitionSeasonFixture;
            $competitionSeasonFixture->setCompetitionSeason($this);
        }

        return $this;
    }

    public function removeCompetitionSeasonFixture(CompetitionSeasonFixture $competitionSeasonFixture): self
    {
        if ($this->competitionSeasonFixtures->contains($competitionSeasonFixture)) {
            $this->competitionSeasonFixtures->removeElement($competitionSeasonFixture);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonFixture->getCompetitionSeason() === $this) {
                $competitionSeasonFixture->setCompetitionSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonTeam[]
     */
    public function getCompetitionSeasonTeams(): Collection
    {
        return $this->competitionSeasonTeams;
    }

    public function addCompetitionSeasonTeam(CompetitionSeasonTeam $competitionSeasonTeam): self
    {
        if (!$this->competitionSeasonTeams->contains($competitionSeasonTeam)) {
            $this->competitionSeasonTeams[] = $competitionSeasonTeam;
            $competitionSeasonTeam->setCompetitionSeason($this);
        }

        return $this;
    }

    public function removeCompetitionSeasonTeam(CompetitionSeasonTeam $competitionSeasonTeam): self
    {
        if ($this->competitionSeasonTeams->contains($competitionSeasonTeam)) {
            $this->competitionSeasonTeams->removeElement($competitionSeasonTeam);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonTeam->getCompetitionSeason() === $this) {
                $competitionSeasonTeam->setCompetitionSeason(null);
            }
        }

        return $this;
    }
}
