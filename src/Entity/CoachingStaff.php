<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CoachingStaffRepository")
 */
class CoachingStaff
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $coach_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $second_coach;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fitness_coach;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $technical_assistants;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $match_analysts;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $gk_coaches;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $club_manager;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $team_manager;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $started_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ended_at;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeamCoachingStaff", mappedBy="coaching_staff")
     */
    private $teamCoachingStaff;

    public function __construct()
    {
        $this->teamCoachingStaff = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoachName(): ?string
    {
        return $this->coach_name;
    }

    public function setCoachName(string $coach_name): self
    {
        $this->coach_name = $coach_name;

        return $this;
    }

    public function getSecondCoach(): ?string
    {
        return $this->second_coach;
    }

    public function setSecondCoach(?string $second_coach): self
    {
        $this->second_coach = $second_coach;

        return $this;
    }

    public function getFitnessCoach(): ?string
    {
        return $this->fitness_coach;
    }

    public function setFitnessCoach(?string $fitness_coach): self
    {
        $this->fitness_coach = $fitness_coach;

        return $this;
    }

    public function getTechnicalAssistants(): ?string
    {
        return $this->technical_assistants;
    }

    public function setTechnicalAssistants(?string $technical_assistants): self
    {
        $this->technical_assistants = $technical_assistants;

        return $this;
    }

    public function getMatchAnalysts(): ?string
    {
        return $this->match_analysts;
    }

    public function setMatchAnalysts(?string $match_analysts): self
    {
        $this->match_analysts = $match_analysts;

        return $this;
    }

    public function getGkCoaches(): ?string
    {
        return $this->gk_coaches;
    }

    public function setGkCoaches(?string $gk_coaches): self
    {
        $this->gk_coaches = $gk_coaches;

        return $this;
    }

    public function getClubManager(): ?string
    {
        return $this->club_manager;
    }

    public function setClubManager(?string $club_manager): self
    {
        $this->club_manager = $club_manager;

        return $this;
    }

    public function getTeamManager(): ?string
    {
        return $this->team_manager;
    }

    public function setTeamManager(?string $team_manager): self
    {
        $this->team_manager = $team_manager;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->started_at;
    }

    public function setStartedAt(?\DateTimeInterface $started_at): self
    {
        $this->started_at = $started_at;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->ended_at;
    }

    public function setEndedAt(?\DateTimeInterface $ended_at): self
    {
        $this->ended_at = $ended_at;

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
     * @return Collection|TeamCoachingStaff[]
     */
    public function getTeamCoachingStaff(): Collection
    {
        return $this->teamCoachingStaff;
    }

    public function addTeamCoachingStaff(TeamCoachingStaff $teamCoachingStaff): self
    {
        if (!$this->teamCoachingStaff->contains($teamCoachingStaff)) {
            $this->teamCoachingStaff[] = $teamCoachingStaff;
            $teamCoachingStaff->setCoachingStaff($this);
        }

        return $this;
    }

    public function removeTeamCoachingStaff(TeamCoachingStaff $teamCoachingStaff): self
    {
        if ($this->teamCoachingStaff->contains($teamCoachingStaff)) {
            $this->teamCoachingStaff->removeElement($teamCoachingStaff);
            // set the owning side to null (unless already changed)
            if ($teamCoachingStaff->getCoachingStaff() === $this) {
                $teamCoachingStaff->setCoachingStaff(null);
            }
        }

        return $this;
    }
}
