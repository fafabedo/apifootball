<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Traits\TimestampableTrait;
use App\Traits\TmkEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MatchSummaryRepository")
 */
class MatchSummary
{
    use TimestampableTrait;
    use TmkEntityTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CompetitionSeasonMatch", inversedBy="matchSummary", cascade={"persist", "remove"})
     */
    private $competitionSeasonMatch;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MatchLineup", mappedBy="matchSummary", cascade={"persist", "remove"})
     * @ApiSubresource()
     */
    private $matchLineups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MatchEvent", mappedBy="matchSummary", cascade={"persist", "remove"})
     * @ApiSubresource()
     */
    private $matchEvents;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stadium")
     */
    private $stadium;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $attendance;

    public function __construct()
    {
        $this->matchLineups = new ArrayCollection();
        $this->matchEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetitionSeasonMatch(): ?CompetitionSeasonMatch
    {
        return $this->competitionSeasonMatch;
    }

    public function setCompetitionSeasonMatch(?CompetitionSeasonMatch $competitionSeasonMatch): self
    {
        $this->competitionSeasonMatch = $competitionSeasonMatch;

        return $this;
    }

    /**
     * @return Collection|MatchLineup[]
     */
    public function getMatchLineups(): Collection
    {
        return $this->matchLineups;
    }

    public function addMatchLineup(MatchLineup $matchLineup): self
    {
        if (!$this->matchLineups->contains($matchLineup)) {
            $this->matchLineups[] = $matchLineup;
            $matchLineup->setMatchSummary($this);
        }

        return $this;
    }

    public function removeMatchLineup(MatchLineup $matchLineup): self
    {
        if ($this->matchLineups->contains($matchLineup)) {
            $this->matchLineups->removeElement($matchLineup);
            // set the owning side to null (unless already changed)
            if ($matchLineup->getMatchSummary() === $this) {
                $matchLineup->setMatchSummary(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MatchEvent[]
     */
    public function getMatchEvents(): Collection
    {
        return $this->matchEvents;
    }

    public function addMatchEvent(MatchEvent $matchEvent): self
    {
        if (!$this->matchEvents->contains($matchEvent)) {
            $this->matchEvents[] = $matchEvent;
            $matchEvent->setMatchSummary($this);
        }

        return $this;
    }

    public function removeMatchEvent(MatchEvent $matchEvent): self
    {
        if ($this->matchEvents->contains($matchEvent)) {
            $this->matchEvents->removeElement($matchEvent);
            // set the owning side to null (unless already changed)
            if ($matchEvent->getMatchSummary() === $this) {
                $matchEvent->setMatchSummary(null);
            }
        }

        return $this;
    }

    public function getStadium(): ?Stadium
    {
        return $this->stadium;
    }

    public function setStadium(?Stadium $stadium): self
    {
        $this->stadium = $stadium;

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
}
