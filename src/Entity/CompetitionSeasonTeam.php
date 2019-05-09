<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"season"}}
 *     )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonTeamRepository")
 */
class CompetitionSeasonTeam
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"season"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeason", inversedBy="competitionSeasonTeams")
     * @Groups({"season"})
     */
    private $competition_season;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="competitionSeasonTeams")
     * @Groups({"season"})
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonTeamPlayer", mappedBy="competition_season_team")
     * @Groups({"season"})
     */
    private $competitionSeasonTeamPlayers;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $groupName;

    /**
     * CompetitionSeasonTeam constructor.
     */
    public function __construct()
    {
        $this->competitionSeasonTeamPlayers = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CompetitionSeason|null
     */
    public function getCompetitionSeason(): ?CompetitionSeason
    {
        return $this->competition_season;
    }

    /**
     * @param CompetitionSeason|null $competition_season
     * @return CompetitionSeasonTeam
     */
    public function setCompetitionSeason(?CompetitionSeason $competition_season): self
    {
        $this->competition_season = $competition_season;

        return $this;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     * @return CompetitionSeasonTeam
     */
    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonTeamPlayer[]
     */
    public function getCompetitionSeasonTeamPlayers(): Collection
    {
        return $this->competitionSeasonTeamPlayers;
    }

    /**
     * @param CompetitionSeasonTeamPlayer $competitionSeasonTeamPlayer
     * @return CompetitionSeasonTeam
     */
    public function addCompetitionSeasonTeamPlayer(CompetitionSeasonTeamPlayer $competitionSeasonTeamPlayer): self
    {
        if (!$this->competitionSeasonTeamPlayers->contains($competitionSeasonTeamPlayer)) {
            $this->competitionSeasonTeamPlayers[] = $competitionSeasonTeamPlayer;
            $competitionSeasonTeamPlayer->setCompetitionSeasonTeam($this);
        }

        return $this;
    }

    /**
     * @param CompetitionSeasonTeamPlayer $competitionSeasonTeamPlayer
     * @return CompetitionSeasonTeam
     */
    public function removeCompetitionSeasonTeamPlayer(CompetitionSeasonTeamPlayer $competitionSeasonTeamPlayer): self
    {
        if ($this->competitionSeasonTeamPlayers->contains($competitionSeasonTeamPlayer)) {
            $this->competitionSeasonTeamPlayers->removeElement($competitionSeasonTeamPlayer);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonTeamPlayer->getCompetitionSeasonTeam() === $this) {
                $competitionSeasonTeamPlayer->setCompetitionSeasonTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"season"})
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * @param string|null $groupName
     * @return CompetitionSeasonTeam
     */
    public function setGroupName(?string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }
}
