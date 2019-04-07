<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"federation"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\FederationRepository")
 */
class Federation
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
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $shortname;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Competition", mappedBy="federation")
     */
    private $competitions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * Federation constructor.
     */
    public function __construct()
    {
        $this->competitions = new ArrayCollection();
    }

    /**
     * @return int|null
     * @Groups({"team", "federation", "country"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     * @Groups({"team", "federation", "country"})
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Federation
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"team", "federation", "country"})
     */
    public function getShortname(): ?string
    {
        return $this->shortname;
    }

    /**
     * @param string|null $shortname
     * @return Federation
     */
    public function setShortname(?string $shortname): self
    {
        $this->shortname = $shortname;

        return $this;
    }

    /**
     * @return Collection|Competition[]
     * @Groups({"hidden"})
     */
    public function getCompetitions(): Collection
    {
        return $this->competitions;
    }

    /**
     * @param Competition $competition
     * @return Federation
     */
    public function addCompetition(Competition $competition): self
    {
        if (!$this->competitions->contains($competition)) {
            $this->competitions[] = $competition;
            $competition->setFederation($this);
        }

        return $this;
    }

    /**
     * @param Competition $competition
     * @return Federation
     */
    public function removeCompetition(Competition $competition): self
    {
        if ($this->competitions->contains($competition)) {
            $this->competitions->removeElement($competition);
            // set the owning side to null (unless already changed)
            if ($competition->getFederation() === $this) {
                $competition->setFederation(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"federation"})
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     * @return Federation
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
