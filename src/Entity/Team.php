<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\MetadataTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "country.id": "exact", "name": "word_start", "competition.id": "exact"})
 */
class Team
{
    use MetadataTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition")
     * @ORM\JoinColumn(nullable=true)
     */
    private $competition;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $shortname;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country")
     * @ORM\JoinColumn(nullable=true)
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonFixtureTeam", mappedBy="team")
     */
    private $competitionSeasonFixtureTeams;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonTeam", mappedBy="team")
     */
    private $competitionSeasonTeams;

    public function __construct()
    {
        $this->competitionSeasonFixtureTeams = new ArrayCollection();
        $this->competitionSeasonTeams = new ArrayCollection();
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
     * @return Team
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getShortname(): ?string
    {
        return $this->shortname;
    }

    /**
     * @param string|null $shortname
     * @return Team
     */
    public function setShortname(?string $shortname): self
    {
        $this->shortname = $shortname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * @param mixed $competition
     * @return Team
     */
    public function setCompetition($competition)
    {
        $this->competition = $competition;
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
     * @return Team
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     * @return Team
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonFixtureTeam[]
     */
    public function getCompetitionSeasonFixtureTeams(): Collection
    {
        return $this->competitionSeasonFixtureTeams;
    }

    public function addCompetitionSeasonFixtureTeam(CompetitionSeasonFixtureTeam $competitionSeasonFixtureTeam): self
    {
        if (!$this->competitionSeasonFixtureTeams->contains($competitionSeasonFixtureTeam)) {
            $this->competitionSeasonFixtureTeams[] = $competitionSeasonFixtureTeam;
            $competitionSeasonFixtureTeam->setTeam($this);
        }

        return $this;
    }

    public function removeCompetitionSeasonFixtureTeam(CompetitionSeasonFixtureTeam $competitionSeasonFixtureTeam): self
    {
        if ($this->competitionSeasonFixtureTeams->contains($competitionSeasonFixtureTeam)) {
            $this->competitionSeasonFixtureTeams->removeElement($competitionSeasonFixtureTeam);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonFixtureTeam->getTeam() === $this) {
                $competitionSeasonFixtureTeam->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonTeam[]
     */
    public function getCompetitionSeasonTeams(): Collection
    {
        return $this->competitionSeasonTeams;
    }

    public function addCompetitionSeasonTeam(CompetitionSeasonTeam $competitionSeasonTeam): self
    {
        if (!$this->competitionSeasonTeams->contains($competitionSeasonTeam)) {
            $this->competitionSeasonTeams[] = $competitionSeasonTeam;
            $competitionSeasonTeam->setTeam($this);
        }

        return $this;
    }

    public function removeCompetitionSeasonTeam(CompetitionSeasonTeam $competitionSeasonTeam): self
    {
        if ($this->competitionSeasonTeams->contains($competitionSeasonTeam)) {
            $this->competitionSeasonTeams->removeElement($competitionSeasonTeam);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonTeam->getTeam() === $this) {
                $competitionSeasonTeam->setTeam(null);
            }
        }

        return $this;
    }



}
