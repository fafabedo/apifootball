<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Traits\MetadataTrait;
use App\Traits\TmkEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"team"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "country.id": "exact", "name": "word_start", "competition.id": "exact", "team_type.id": "exact"})
 */
class Team
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
     * @ORM\Column(type="text")
     * @Groups("competition_season")
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
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonTeam", mappedBy="team")
     */
    private $competitionSeasonTeams;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TeamType")
     */
    private $team_type;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_youth_team;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeamCoachingStaff", mappedBy="team", orphanRemoval=true)
     */
    private $teamCoachingStaff;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeamAward", mappedBy="team", orphanRemoval=true)
     */
    private $teamAwards;

    /**
     * Team constructor.
     */
    public function __construct()
    {
        $this->competitionSeasonMatchTeams = new ArrayCollection();
        $this->competitionSeasonTeams = new ArrayCollection();
        $this->teamCoachingStaff = new ArrayCollection();
        $this->teamAwards = new ArrayCollection();
    }

    /**
     * @return int|null
     * @Groups({"team", "fixture", "season"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     * @Groups({"team", "season"})
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
     * @Groups({"team", "season"})
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
     * @Groups({"team", "season"})
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
     * @Groups({"team", "season"})
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
     * @return Collection|CompetitionSeasonTeam[]
     */
    public function getCompetitionSeasonTeams(): Collection
    {
        return $this->competitionSeasonTeams;
    }

    /**
     * @param CompetitionSeasonTeam $competitionSeasonTeam
     * @return Team
     */
    public function addCompetitionSeasonTeam(CompetitionSeasonTeam $competitionSeasonTeam): self
    {
        if (!$this->competitionSeasonTeams->contains($competitionSeasonTeam)) {
            $this->competitionSeasonTeams[] = $competitionSeasonTeam;
            $competitionSeasonTeam->setTeam($this);
        }

        return $this;
    }

    /**
     * @param CompetitionSeasonTeam $competitionSeasonTeam
     * @return Team
     */
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

    /**
     * @return TeamType|null
     * @Groups({"team", "season"})
     */
    public function getTeamType(): ?TeamType
    {
        return $this->team_type;
    }

    /**
     * @param TeamType|null $team_type
     * @return Team
     */
    public function setTeamType(?TeamType $team_type): self
    {
        $this->team_type = $team_type;

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"team"})
     */
    public function getIsYouthTeam(): ?bool
    {
        return $this->is_youth_team;
    }

    /**
     * @param bool|null $is_youth_team
     * @return Team
     */
    public function setIsYouthTeam(?bool $is_youth_team): self
    {
        $this->is_youth_team = $is_youth_team;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"team"})
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     * @return Team
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"team"})
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     * @return Team
     */
    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|TeamCoachingStaff[]
     */
    public function getTeamCoachingStaff(): Collection
    {
        return $this->teamCoachingStaff;
    }

    /**
     * @param TeamCoachingStaff $teamCoachingStaff
     * @return Team
     */
    public function addTeamCoachingStaff(TeamCoachingStaff $teamCoachingStaff): self
    {
        if (!$this->teamCoachingStaff->contains($teamCoachingStaff)) {
            $this->teamCoachingStaff[] = $teamCoachingStaff;
            $teamCoachingStaff->setTeam($this);
        }

        return $this;
    }

    /**
     * @param TeamCoachingStaff $teamCoachingStaff
     * @return Team
     */
    public function removeTeamCoachingStaff(TeamCoachingStaff $teamCoachingStaff): self
    {
        if ($this->teamCoachingStaff->contains($teamCoachingStaff)) {
            $this->teamCoachingStaff->removeElement($teamCoachingStaff);
            // set the owning side to null (unless already changed)
            if ($teamCoachingStaff->getTeam() === $this) {
                $teamCoachingStaff->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TeamAward[]
     */
    public function getTeamAwards(): Collection
    {
        return $this->teamAwards;
    }

    public function addTeamAward(TeamAward $teamAward): self
    {
        if (!$this->teamAwards->contains($teamAward)) {
            $this->teamAwards[] = $teamAward;
            $teamAward->setTeam($this);
        }

        return $this;
    }

    public function removeTeamAward(TeamAward $teamAward): self
    {
        if ($this->teamAwards->contains($teamAward)) {
            $this->teamAwards->removeElement($teamAward);
            // set the owning side to null (unless already changed)
            if ($teamAward->getTeam() === $this) {
                $teamAward->setTeam(null);
            }
        }

        return $this;
    }




}
