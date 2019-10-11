<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"type"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\PositionRepository")
 */
class Position
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
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $shortName;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PositionMap", mappedBy="position", cascade={"persist", "remove"})
     */
    private $positionMap;


    public function __construct()
    {
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
     * @return Position
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getPositionMap(): ?PositionMap
    {
        return $this->positionMap;
    }

    public function setPositionMap(PositionMap $positionMap): self
    {
        $this->positionMap = $positionMap;

        // set the owning side of the relation if necessary
        if ($this !== $positionMap->getPosition()) {
            $positionMap->setPosition($this);
        }

        return $this;
    }

}
