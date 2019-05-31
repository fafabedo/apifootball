<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PlayerMaketValueRepository")
 */
class PlayerMaketValue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="playerMaketValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $age;

  /**
   * @return int|null
   */
  public function getId(): ?int
    {
        return $this->id;
    }

  /**
   * @return \App\Entity\Player|null
   */
  public function getPlayer(): ?Player
    {
        return $this->player;
    }

  /**
   * @param \App\Entity\Player|null $player
   *
   * @return \App\Entity\PlayerMaketValue
   */
  public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

  /**
   * @return float|null
   */
  public function getValue(): ?float
    {
        return $this->value;
    }

  /**
   * @param float $value
   *
   * @return \App\Entity\PlayerMaketValue
   */
  public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

  /**
   * @return \App\Entity\Team|null
   */
  public function getTeam(): ?Team
    {
        return $this->team;
    }

  /**
   * @param \App\Entity\Team|null $team
   *
   * @return \App\Entity\PlayerMaketValue
   */
  public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

  /**
   * @return int|null
   */
  public function getAge(): ?int
    {
        return $this->age;
    }

  /**
   * @param int|null $age
   *
   * @return \App\Entity\PlayerMaketValue
   */
  public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }
}
