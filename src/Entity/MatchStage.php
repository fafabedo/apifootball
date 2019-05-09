<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MatchStageRepository")
 */
class MatchStage
{
    const MATCH_STAGE_LEAGUE = 'League';
    const MATCH_STAGE_GROUP = 'Group';
    const MATCH_STAGE_PLAYOFF = 'Playoff';
    const MATCH_STAGE_ROUND_32 = 'Round of 32';
    const MATCH_STAGE_ROUND_16 = 'Round of 16';
    const MATCH_STAGE_QUARTER_FINAL = 'Quarter-Finals';
    const MATCH_STAGE_SEMI_FINAL = 'Semi-Finals';
    const MATCH_STAGE_THIRD_PLACE = 'Third Place';
    const MATCH_STAGE_FINAL = 'Final';

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
     * @Groups({"fixture"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     * @Groups({"fixture"})
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MatchStage
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
