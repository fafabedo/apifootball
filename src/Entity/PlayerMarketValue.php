<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PlayerMarketValueRepository")
 */
class PlayerMarketValue
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="playerMarketValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Team;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

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
     * @return PlayerMarketValue
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
        return $this->Team;
    }

    /**
     * @param Team|null $Team
     * @return PlayerMarketValue
     */
    public function setTeam(?Team $Team): self
    {
        $this->Team = $Team;

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
     * @return PlayerMarketValue
     */
    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     * @return PlayerMarketValue
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     * @return PlayerMarketValue
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }


}
