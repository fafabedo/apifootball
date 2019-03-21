<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\MetadataTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionRepository")
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "country.id": "exact", "name": "partial"})
 */
class Competition
{
    use MetadataTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\League", inversedBy="competitions")
     */
    private $league;

    /**
     * @ORM\Column(type="integer")
     */
    private $league_level;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\Column(type="integer")
     */
    private $number_teams;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionType")
     * @ORM\JoinColumn(nullable=true)
     */
    private $competition_type;

    /**
     * @return int|null
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
     * @return League|null
     */
    public function getLeague(): ?League
    {
        return $this->league;
    }

    /**
     * @param League|null $league
     * @return Competition
     */
    public function setLeague(?League $league): self
    {
        $this->league = $league;

        return $this;
    }

    /**
     * @return int|null
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
     * @return mixed
     */
    public function getCompetitionType()
    {
        return $this->competition_type;
    }

    /**
     * @param mixed $competition_type
     * @return Competition
     */
    public function setCompetitionType($competition_type)
    {
        $this->competition_type = $competition_type;
        return $this;
    }

}
