<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\TmkEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"fixture"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonMatchRepository")
 * @ApiFilter(SearchFilter::class, properties={"competition_season.id": "exact"})
 */
class CompetitionSeasonMatch
{
    use ORMBehaviors\Timestampable\Timestampable;
    use TmkEntityTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     * @Groups({"fixture"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeason", inversedBy="competitionSeasonMatches")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competition_season;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $match_datetime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $match_day;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $metadata = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $attendance;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonMatchTeam", mappedBy="competition_season_match", orphanRemoval=true, cascade={"persist", "remove"})
     * @Groups({"fixture"})
     */
    private $competitionSeasonMatchTeams;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isProcessed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isPlayed;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MatchStage")
     * @ORM\JoinColumn(nullable=false)
     */
    private $MatchStage;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $MatchGroup;

    /**
     * CompetitionSeasonMatch constructor.
     */
    public function __construct()
    {
        $this->competitionSeasonMatchTeams = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CompetitionSeason|null
     * @Groups({"fixture"})
     */
    public function getCompetitionSeason(): ?CompetitionSeason
    {
        return $this->competition_season;
    }

    /**
     * @param CompetitionSeason|null $competition_season
     * @return CompetitionSeasonMatch
     */
    public function setCompetitionSeason(?CompetitionSeason $competition_season): self
    {
        $this->competition_season = $competition_season;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     * @Groups({"fixture", "season"})
     */
    public function getMatchDatetime(): ?\DateTimeInterface
    {
        return $this->match_datetime;
    }

    /**
     * @param \DateTimeInterface|null $match_datetime
     * @return CompetitionSeasonMatch
     */
    public function setMatchDatetime(?\DateTimeInterface $match_datetime): self
    {
        $this->match_datetime = $match_datetime;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"fixture", "season"})
     */
    public function getMatchDay(): ?int
    {
        return $this->match_day;
    }

    /**
     * @param int|null $match_day
     * @return CompetitionSeasonMatch
     */
    public function setMatchDay(?int $match_day): self
    {
        $this->match_day = $match_day;

        return $this;
    }

    /**
     * @return array|null
     * @Groups({"fixture", "season"})
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @param array|null $metadata
     * @return CompetitionSeasonMatch
     */
    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"fixture"})
     */
    public function getAttendance(): ?int
    {
        return $this->attendance;
    }

    /**
     * @param int|null $attendance
     * @return CompetitionSeasonMatch
     */
    public function setAttendance(?int $attendance): self
    {
        $this->attendance = $attendance;

        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonMatchTeam[]
     */
    public function getCompetitionSeasonMatchTeams(): Collection
    {
        return $this->competitionSeasonMatchTeams;
    }

    /**
     * @param CompetitionSeasonMatchTeam $competitionSeasonMatchTeam
     * @return CompetitionSeasonMatch
     */
    public function addCompetitionSeasonMatchTeam(CompetitionSeasonMatchTeam $competitionSeasonMatchTeam): self
    {
        if (!$this->competitionSeasonMatchTeams->contains($competitionSeasonMatchTeam)) {
            $this->competitionSeasonMatchTeams[] = $competitionSeasonMatchTeam;
            $competitionSeasonMatchTeam->setCompetitionSeasonMatch($this);
        }

        return $this;
    }

    /**
     * @param CompetitionSeasonMatchTeam $competitionSeasonMatchTeam
     * @return CompetitionSeasonMatch
     */
    public function removeCompetitionSeasonMatchTeam(CompetitionSeasonMatchTeam $competitionSeasonMatchTeam): self
    {
        if ($this->competitionSeasonMatchTeams->contains($competitionSeasonMatchTeam)) {
            $this->competitionSeasonMatchTeams->removeElement($competitionSeasonMatchTeam);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonMatchTeam->getCompetitionSeasonMatch() === $this) {
                $competitionSeasonMatchTeam->setCompetitionSeasonMatch(null);
            }
        }

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"fixture", "season"})
     */
    public function getIsProcessed(): ?bool
    {
        return $this->isProcessed;
    }

    /**
     * @param bool $isProcessed
     * @return CompetitionSeasonMatch
     */
    public function setIsProcessed(bool $isProcessed): self
    {
        $this->isProcessed = $isProcessed;

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"fixture", "season"})
     */
    public function getIsPlayed(): ?bool
    {
        return $this->isPlayed;
    }

    /**
     * @param bool|null $isPlayed
     * @return CompetitionSeasonMatch
     */
    public function setIsPlayed(?bool $isPlayed): self
    {
        $this->isPlayed = $isPlayed;

        return $this;
    }

    /**
     * @return MatchStage|null
     * @Groups({"fixture"})
     */
    public function getMatchStage(): ?MatchStage
    {
        return $this->MatchStage;
    }

    /**
     * @param MatchStage|null $MatchStage
     * @return CompetitionSeasonMatch
     */
    public function setMatchStage(?MatchStage $MatchStage): self
    {
        $this->MatchStage = $MatchStage;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"fixture", "season"})
     */
    public function getMatchGroup(): ?string
    {
        return $this->MatchGroup;
    }

    /**
     * @param string|null $MatchGroup
     * @return CompetitionSeasonMatch
     */
    public function setMatchGroup(?string $MatchGroup): self
    {
        $this->MatchGroup = $MatchGroup;

        return $this;
    }

}
