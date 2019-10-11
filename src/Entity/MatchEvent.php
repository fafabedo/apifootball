<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MatchEventRepository")
 */
class MatchEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MatchSummary", inversedBy="matchEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $matchSummary;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EventType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eventType;

    /**
     * @ORM\Column(type="integer")
     */
    private $minute;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mainPlayer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player")
     */
    private $subPlayer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatchSummary(): ?MatchSummary
    {
        return $this->matchSummary;
    }

    public function setMatchSummary(?MatchSummary $matchSummary): self
    {
        $this->matchSummary = $matchSummary;

        return $this;
    }

    public function getEventType(): ?EventType
    {
        return $this->eventType;
    }

    public function setEventType(?EventType $eventType): self
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getMinute(): ?int
    {
        return $this->minute;
    }

    public function setMinute(int $minute): self
    {
        $this->minute = $minute;

        return $this;
    }

    public function getMainPlayer(): ?Player
    {
        return $this->mainPlayer;
    }

    public function setMainPlayer(?Player $mainPlayer): self
    {
        $this->mainPlayer = $mainPlayer;

        return $this;
    }

    public function getSubPlayer(): ?Player
    {
        return $this->subPlayer;
    }

    public function setSubPlayer(?Player $subPlayer): self
    {
        $this->subPlayer = $subPlayer;

        return $this;
    }
}
