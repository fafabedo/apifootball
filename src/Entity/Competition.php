<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\MetadataTrait;
use App\Traits\TmkEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"competition", "country", "type"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionRepository")
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "country.id": "exact", "name": "partial", "team_type.id": "exact", "federation.id": "exact", "isFeatured":"exact"})
 */
class Competition
{
    use MetadataTrait;
    use TmkEntityTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $league_level;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @ORM\JoinColumn(nullable=true)
     * @MaxDepth(1)
     */
    private $country;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number_teams;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeason", mappedBy="competition", cascade={"persist", "remove"})
     * @ApiSubresource()
     */
    private $competitionSeasons;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Federation", inversedBy="competitions")
     */
    private $federation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TeamType")
     */
    private $team_type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionType")
     */
    private $competition_type;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_youth_competition;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isFeatured;

    /**
     * Competition constructor.
     */
    public function __construct()
    {
        $this->competitionSeasons = new ArrayCollection();
    }


    /**
     * @return int|null
     * @Groups({"competition"})
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return Competition
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     * @Groups({"competition"})
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Competition
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"competition"})
     */
    public function getLeagueLevel(): ?int
    {
        return $this->league_level;
    }

    /**
     * @param int $league_level
     * @return Competition
     */
    public function setLeagueLevel(int $league_level): self
    {
        $this->league_level = $league_level;

        return $this;
    }

    /**
     * @return Country|null
     * @Groups({"competition"})
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country|null $country
     * @return Competition
     */
    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"competition"})
     */
    public function getNumberTeams(): ?int
    {
        return $this->number_teams;
    }

    /**
     * @param int $number_teams
     * @return Competition
     */
    public function setNumberTeams(int $number_teams): self
    {
        $this->number_teams = $number_teams;

        return $this;
    }

    /**
     * @return Collection|CompetitionSeason[]
     * @Groups({"read"})
     */
    public function getCompetitionSeasons(): Collection
    {
        return $this->competitionSeasons;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return Competition
     */
    public function addCompetitionSeason(CompetitionSeason $competitionSeason): self
    {
        if (!$this->competitionSeasons->contains($competitionSeason)) {
            $this->competitionSeasons[] = $competitionSeason;
            $competitionSeason->setCompetition($this);
        }

        return $this;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return Competition
     */
    public function removeCompetitionSeason(CompetitionSeason $competitionSeason): self
    {
        if ($this->competitionSeasons->contains($competitionSeason)) {
            $this->competitionSeasons->removeElement($competitionSeason);
            // set the owning side to null (unless already changed)
            if ($competitionSeason->getCompetition() === $this) {
                $competitionSeason->setCompetition(null);
            }
        }

        return $this;
    }

    /**
     * @return Federation|null
     * @Groups({"competition"})
     */
    public function getFederation(): ?Federation
    {
        return $this->federation;
    }

    /**
     * @param Federation|null $federation
     * @return Competition
     */
    public function setFederation(?Federation $federation): self
    {
        $this->federation = $federation;

        return $this;
    }

    /**
     * @return TeamType|null
     * @Groups({"competition"})
     */
    public function getTeamType(): ?TeamType
    {
        return $this->team_type;
    }

    /**
     * @param TeamType|null $team_type
     * @return Competition
     */
    public function setTeamType(?TeamType $team_type): self
    {
        $this->team_type = $team_type;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"competition"})
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     * @return Competition
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"competition"})
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     * @return Competition
     */
    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return CompetitionType|null
     * @Groups({"competition"})
     */
    public function getCompetitionType(): ?CompetitionType
    {
        return $this->competition_type;
    }

    /**
     * @param CompetitionType|null $competition_type
     * @return Competition
     */
    public function setCompetitionType(?CompetitionType $competition_type): self
    {
        $this->competition_type = $competition_type;

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"competition"})
     */
    public function getIsYouthCompetition(): ?bool
    {
        return $this->is_youth_competition;
    }

    /**
     * @param bool|null $is_youth_competition
     * @return Competition
     */
    public function setIsYouthCompetition(?bool $is_youth_competition): self
    {
        $this->is_youth_competition = $is_youth_competition;

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"competition"})
     */
    public function getIsFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    /**
     * @param bool|null $isFeatured
     * @return Competition
     */
    public function setIsFeatured(?bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

}
