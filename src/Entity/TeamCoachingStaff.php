<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\TeamCoachingStaffRepository")
 */
class TeamCoachingStaff
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="teamCoachingStaff")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CoachingStaff", inversedBy="teamCoachingStaff")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coaching_staff;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCoachingStaff(): ?CoachingStaff
    {
        return $this->coaching_staff;
    }

    public function setCoachingStaff(?CoachingStaff $coaching_staff): self
    {
        $this->coaching_staff = $coaching_staff;

        return $this;
    }
}
