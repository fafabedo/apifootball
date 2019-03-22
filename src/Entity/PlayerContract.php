<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PlayerContractRepository")
 */
class PlayerContract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="playerContracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_at;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_at;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archive;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=2, nullable=true)
     */
    private $annual_salary;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $joined_to_team_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->start_at;
    }

    public function setStartAt(?\DateTimeInterface $start_at): self
    {
        $this->start_at = $start_at;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->end_at;
    }

    public function setEndAt(?\DateTimeInterface $end_at): self
    {
        $this->end_at = $end_at;

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

    public function getAnnualSalary()
    {
        return $this->annual_salary;
    }

    public function setAnnualSalary($annual_salary): self
    {
        $this->annual_salary = $annual_salary;

        return $this;
    }

    public function getJoinedToTeamAt(): ?\DateTimeInterface
    {
        return $this->joined_to_team_at;
    }

    public function setJoinedToTeamAt(?\DateTimeInterface $joined_to_team_at): self
    {
        $this->joined_to_team_at = $joined_to_team_at;

        return $this;
    }
}
