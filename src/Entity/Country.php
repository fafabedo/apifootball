<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\MetadataTrait;
use App\Traits\TmkEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"country"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CountryRepository")
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "name": "partial", "code": "partial", "active": "exact"})
 */
class Country
{
    use MetadataTrait;
    use TmkEntityTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default"=false})
     */
    private $active;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Federation")
     */
    private $federation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @return int|null
     * @Groups({"team", "country"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     * @Groups({"team", "country"})
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Country
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     * @Groups({"team", "country"})
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     * @return Country
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return mixed
     * @Groups({"team", "country"})
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return Country
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return Federation|null
     * @Groups({"team", "country"})
     */
    public function getFederation(): ?Federation
    {
        return $this->federation;
    }

    /**
     * @param Federation|null $federation
     * @return Country
     */
    public function setFederation(?Federation $federation): self
    {
        $this->federation = $federation;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"country"})
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     * @return Country
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
