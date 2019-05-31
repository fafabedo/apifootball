<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\AwardRepository")
 */
class Award
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition")
     * @ORM\JoinColumn(nullable=false)
     */
    private $competition;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeamAward", mappedBy="award", orphanRemoval=true)
     */
    private $teamAwards;

  /**
   * Award constructor.
   */
  public function __construct()
    {
        $this->teamAwards = new ArrayCollection();
    }

  /**
   * @return int|null
   */
  public function getId(): ?int
    {
        return $this->id;
    }

  /**
   * @return string|null
   */
  public function getName(): ?string
    {
        return $this->name;
    }

  /**
   * @param string $name
   *
   * @return \App\Entity\Award
   */
  public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

  /**
   * @return string|null
   */
  public function getImage(): ?string
    {
        return $this->image;
    }

  /**
   * @param string|null $image
   *
   * @return \App\Entity\Award
   */
  public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

  /**
   * @return \App\Entity\Competition|null
   */
  public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

  /**
   * @param \App\Entity\Competition|null $competition
   *
   * @return \App\Entity\Award
   */
  public function setCompetition(?Competition $competition): self
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * @return Collection|TeamAward[]
     */
    public function getTeamAwards(): Collection
    {
        return $this->teamAwards;
    }

  /**
   * @param \App\Entity\TeamAward $teamAward
   *
   * @return \App\Entity\Award
   */
  public function addTeamAward(TeamAward $teamAward): self
    {
        if (!$this->teamAwards->contains($teamAward)) {
            $this->teamAwards[] = $teamAward;
            $teamAward->setAward($this);
        }

        return $this;
    }

  /**
   * @param \App\Entity\TeamAward $teamAward
   *
   * @return \App\Entity\Award
   */
  public function removeTeamAward(TeamAward $teamAward): self
    {
        if ($this->teamAwards->contains($teamAward)) {
            $this->teamAwards->removeElement($teamAward);
            // set the owning side to null (unless already changed)
            if ($teamAward->getAward() === $this) {
                $teamAward->setAward(null);
            }
        }

        return $this;
    }
}
