<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Traits\MetadataTrait;
use App\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read", "write"}}
 *     )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonRepository")
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "competition.id": "exact", "archive": "exact"})
 */
class CompetitionSeason
{
    use MetadataTrait;
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
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
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $competition;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonTeam", mappedBy="competition_season", cascade={"persist", "remove"})
     * @ApiSubresource()
     */
    private $competitionSeasonTeams;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $metadata;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonMatch", mappedBy="competition_season", orphanRemoval=true)
     * @ApiSubresource()
     */
    private $competitionSeasonMatches;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonTable", mappedBy="competitionSeason")
     * @ApiSubresource()
     */
    private $competitionSeasonTables;

    /**
     * CompetitionSeason constructor.
     */
    public function __construct()
    {
        $this->competitionSeasonTeams = new ArrayCollection();
        $this->competitionSeasonMatches = new ArrayCollection();
        $this->competitionSeasonTables = new ArrayCollection();
    }

    /**
     * @return int|null
     * @Groups({"read"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface|null
     * @Groups({"read"})
     */
    public function getStartSeason(): ?\DateTimeInterface
    {
        return $this->start_season;
    }

    /**
     * @param \DateTimeInterface $start_season
     * @return CompetitionSeason
     */
    public function setStartSeason(\DateTimeInterface $start_season): self
    {
        $this->start_season = $start_season;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     * @Groups({"read"})
     */
    public function getEndSeason(): ?\DateTimeInterface
    {
        return $this->end_season;
    }

    /**
     * @param \DateTimeInterface $end_season
     * @return CompetitionSeason
     */
    public function setEndSeason(\DateTimeInterface $end_season): self
    {
        $this->end_season = $end_season;

        return $this;
    }

    /**
     * @return Competition|null
     */
    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition|null $competition
     * @return CompetitionSeason
     */
    public function setCompetition(?Competition $competition): self
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"read"})
     */
    public function getArchive(): ?bool
    {
        return $this->archive;
    }

    /**
     * @param bool|null $archive
     * @return CompetitionSeason
     */
    public function setArchive(?bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonTeam[]
     * @Groups({"read"})
     */
    public function getCompetitionSeasonTeams(): Collection
    {
        return $this->competitionSeasonTeams;
    }

    /**
     * @param CompetitionSeasonTeam $competitionSeasonTeam
     * @return CompetitionSeason
     */
    public function addCompetitionSeasonTeam(CompetitionSeasonTeam $competitionSeasonTeam): self
    {
        if (!$this->competitionSeasonTeams->contains($competitionSeasonTeam)) {
            $this->competitionSeasonTeams[] = $competitionSeasonTeam;
            $competitionSeasonTeam->setCompetitionSeason($this);
        }

        return $this;
    }

    /**
     * @param CompetitionSeasonTeam $competitionSeasonTeam
     * @return CompetitionSeason
     */
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

    /**
     * @return Collection|CompetitionSeasonMatch[]
     */
    public function getCompetitionSeasonMatches(): Collection
    {
        return $this->competitionSeasonMatches;
    }

    /**
     * @param CompetitionSeasonMatch $competitionSeasonMatch
     * @return CompetitionSeason
     */
    public function addCompetitionSeasonMatch(CompetitionSeasonMatch $competitionSeasonMatch): self
    {
        if (!$this->competitionSeasonMatches->contains($competitionSeasonMatch)) {
            $this->competitionSeasonMatches[] = $competitionSeasonMatch;
            $competitionSeasonMatch->setCompetitionSeason($this);
        }

        return $this;
    }

    /**
     * @param CompetitionSeasonMatch $competitionSeasonMatch
     * @return CompetitionSeason
     */
    public function removeCompetitionSeasonMatch(CompetitionSeasonMatch $competitionSeasonMatch): self
    {
        if ($this->competitionSeasonMatches->contains($competitionSeasonMatch)) {
            $this->competitionSeasonMatches->removeElement($competitionSeasonMatch);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonMatch->getCompetitionSeason() === $this) {
                $competitionSeasonMatch->setCompetitionSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonTable[]
     */
    public function getCompetitionSeasonTables(): Collection
    {
        return $this->competitionSeasonTables;
    }

    /**
     * @param CompetitionSeasonTable $competitionSeasonTable
     * @return CompetitionSeason
     */
    public function addCompetitionSeasonTable(CompetitionSeasonTable $competitionSeasonTable): self
    {
        if (!$this->competitionSeasonTables->contains($competitionSeasonTable)) {
            $this->competitionSeasonTables[] = $competitionSeasonTable;
            $competitionSeasonTable->setCompetitionSeason($this);
        }

        return $this;
    }

    /**
     * @param CompetitionSeasonTable $competitionSeasonTable
     * @return CompetitionSeason
     */
    public function removeCompetitionSeasonTable(CompetitionSeasonTable $competitionSeasonTable): self
    {
        if ($this->competitionSeasonTables->contains($competitionSeasonTable)) {
            $this->competitionSeasonTables->removeElement($competitionSeasonTable);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonTable->getCompetitionSeason() === $this) {
                $competitionSeasonTable->setCompetitionSeason(null);
            }
        }

        return $this;
    }
}
