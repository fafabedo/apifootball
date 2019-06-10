<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Traits\MetadataTrait;
use App\Traits\TmkEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 */
class Player
{
    use MetadataTrait;
    use TmkEntityTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint", name="id")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(name="shortname", type="string", length=50, nullable=true)
     * @Assert\NotBlank()
     */
    private $shortname;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $place_of_birth;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $picture;

    /**
     * @var string|null
     *
     * @ORM\Column(name="foot", type="string", length=15, nullable=true)
     */
    private $foot;

    /**
     * @var string|null
     *
     * @ORM\Column(name="outfitter", type="string", length=100, nullable=true)
     */
    private $outfitter;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Agent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    private $agent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerPosition", mappedBy="player")
     */
    private $playerPositions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerContract", mappedBy="player")
     */
    private $playerContracts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $jerseyNumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlayerMarketValue", mappedBy="player", orphanRemoval=true)
     */
    private $playerMarketValues;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $FullName;

    /**
     * Player constructor.
     */
    public function __construct()
    {
        $this->playerPositions = new ArrayCollection();
        $this->playerContracts = new ArrayCollection();
        $this->playerMarketValues = new ArrayCollection();
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
     * @return Player
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortname()
    {
        return $this->shortname;
    }

    /**
     * @param mixed $shortname
     * @return Player
     */
    public function setShortname($shortname)
    {
        $this->shortname = $shortname;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    /**
     * @param \DateTimeInterface|null $birthday
     * @return Player
     */
    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlaceOfBirth(): ?string
    {
        return $this->place_of_birth;
    }

    /**
     * @param string|null $place_of_birth
     * @return Player
     */
    public function setPlaceOfBirth(?string $place_of_birth): self
    {
        $this->place_of_birth = $place_of_birth;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * @param float|null $height
     * @return Player
     */
    public function setHeight(?float $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return Player
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     * @return Player
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFoot(): ?string
    {
        return $this->foot;
    }

    /**
     * @param string|null $foot
     * @return Player
     */
    public function setFoot(?string $foot): Player
    {
        $this->foot = $foot;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOutfitter(): ?string
    {
        return $this->outfitter;
    }

    /**
     * @param string|null $outfitter
     * @return Player
     */
    public function setOutfitter(?string $outfitter): Player
    {
        $this->outfitter = $outfitter;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param mixed $agent
     * @return Player
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @return Collection|PlayerPosition[]
     */
    public function getPlayerPositions(): Collection
    {
        return $this->playerPositions;
    }

    public function addPlayerPosition(PlayerPosition $playerPosition): self
    {
        if (!$this->playerPositions->contains($playerPosition)) {
            $this->playerPositions[] = $playerPosition;
            $playerPosition->setPlayer($this);
        }

        return $this;
    }

    public function removePlayerPosition(PlayerPosition $playerPosition): self
    {
        if ($this->playerPositions->contains($playerPosition)) {
            $this->playerPositions->removeElement($playerPosition);
            // set the owning side to null (unless already changed)
            if ($playerPosition->getPlayer() === $this) {
                $playerPosition->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PlayerContract[]
     */
    public function getPlayerContracts(): Collection
    {
        return $this->playerContracts;
    }

    public function addPlayerContract(PlayerContract $playerContract): self
    {
        if (!$this->playerContracts->contains($playerContract)) {
            $this->playerContracts[] = $playerContract;
            $playerContract->setPlayer($this);
        }

        return $this;
    }

    public function removePlayerContract(PlayerContract $playerContract): self
    {
        if ($this->playerContracts->contains($playerContract)) {
            $this->playerContracts->removeElement($playerContract);
            // set the owning side to null (unless already changed)
            if ($playerContract->getPlayer() === $this) {
                $playerContract->setPlayer(null);
            }
        }

        return $this;
    }

    public function getJerseyNumber(): ?int
    {
        return $this->jerseyNumber;
    }

    public function setJerseyNumber(?int $jerseyNumber): self
    {
        $this->jerseyNumber = $jerseyNumber;

        return $this;
    }

    /**
     * @return Collection|PlayerMarketValue[]
     */
    public function getPlayerMarketValues(): Collection
    {
        return $this->playerMarketValues;
    }

    public function addPlayerMarketValue(PlayerMarketValue $playerMarketValue): self
    {
        if (!$this->playerMarketValues->contains($playerMarketValue)) {
            $this->playerMarketValues[] = $playerMarketValue;
            $playerMarketValue->setPlayer($this);
        }

        return $this;
    }

    public function removePlayerMarketValue(PlayerMarketValue $playerMarketValue): self
    {
        if ($this->playerMarketValues->contains($playerMarketValue)) {
            $this->playerMarketValues->removeElement($playerMarketValue);
            // set the owning side to null (unless already changed)
            if ($playerMarketValue->getPlayer() === $this) {
                $playerMarketValue->setPlayer(null);
            }
        }

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->FullName;
    }

    public function setFullName(?string $FullName): self
    {
        $this->FullName = $FullName;

        return $this;
    }
}
