<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"type"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionTypeRepository")
 */
class CompetitionType
{
    const TOURNAMENT = 1;
    const LEAGUE = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
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
     * @param string|null $name
     * @return CompetitionType
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
