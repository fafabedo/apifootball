<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PositionMapRepository")
 */
class PositionMap
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Position", inversedBy="positionMap", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $position;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $topPosition;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bottomPosition;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leftPosition;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rightPosition;


    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Position|null
     */
    public function getPosition(): ?Position
    {
        return $this->position;
    }

    /**
     * @param Position $position
     * @return PositionMap
     */
    public function setPosition(Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTopPosition(): ?int
    {
        return $this->topPosition;
    }

    /**
     * @param int|null $topPosition
     * @return PositionMap
     */
    public function setTopPosition(?int $topPosition): self
    {
        $this->topPosition = $topPosition;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBottomPosition(): ?int
    {
        return $this->bottomPosition;
    }

    /**
     * @param int|null $bottomPosition
     * @return PositionMap
     */
    public function setBottomPosition(?int $bottomPosition): self
    {
        $this->bottomPosition = $bottomPosition;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLeftPosition(): ?int
    {
        return $this->leftPosition;
    }

    /**
     * @param int|null $leftPosition
     * @return PositionMap
     */
    public function setLeftPosition(?int $leftPosition): self
    {
        $this->leftPosition = $leftPosition;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRightPosition(): ?int
    {
        return $this->rightPosition;
    }

    /**
     * @param int|null $rightPosition
     * @return PositionMap
     */
    public function setRightPosition(?int $rightPosition): self
    {
        $this->rightPosition = $rightPosition;

        return $this;
    }

}
