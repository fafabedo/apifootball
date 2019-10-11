<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"type"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\EventTypeRepository")
 */
class EventType
{
    const EVENT_SUBS = 'subs';
    const EVENT_YELLOW_CARD = 'yellow_card';
    const EVENT_RED_CARD = 'red_card';
    const EVENT_SCORE = 'score';

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
     * @return int|null
     * @Groups({"type"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     * @Groups({"type"})
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return EventType
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
