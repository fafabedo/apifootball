<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"player"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\PlayerContractRepository")
 */
class PlayerContract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @param Player|null $player
     * @return PlayerContract
     */
    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
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
     * @return PlayerContract
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->start_at;
    }

    /**
     * @param \DateTimeInterface|null $start_at
     * @return PlayerContract
     */
    public function setStartAt(?\DateTimeInterface $start_at): self
    {
        $this->start_at = $start_at;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->end_at;
    }

    /**
     * @param \DateTimeInterface|null $end_at
     * @return PlayerContract
     */
    public function setEndAt(?\DateTimeInterface $end_at): self
    {
        $this->end_at = $end_at;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getArchive(): ?bool
    {
        return $this->archive;
    }

    /**
     * @param bool|null $archive
     * @return PlayerContract
     */
    public function setArchive(?bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnnualSalary()
    {
        return $this->annual_salary;
    }

    /**
     * @param $annual_salary
     * @return PlayerContract
     */
    public function setAnnualSalary($annual_salary): self
    {
        $this->annual_salary = $annual_salary;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getJoinedToTeamAt(): ?\DateTimeInterface
    {
        return $this->joined_to_team_at;
    }

    /**
     * @param \DateTimeInterface|null $joined_to_team_at
     * @return PlayerContract
     */
    public function setJoinedToTeamAt(?\DateTimeInterface $joined_to_team_at): self
    {
        $this->joined_to_team_at = $joined_to_team_at;

        return $this;
    }
}
