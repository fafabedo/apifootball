<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"table"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionSeasonTableRepository")
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "competition_season.id": "exact", "match_day": "exact"})
 */
class CompetitionSeasonTable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"table"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CompetitionSeason")
     * @ORM\JoinColumn(nullable=false)
     */
    private $competitionSeason;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $match_day;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $timestamp;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompetitionSeasonTableItem", mappedBy="competition_season_table", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $competitionSeasonTableItems;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isProcessed;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $groupName;

    /**
     * CompetitionSeasonTable constructor.
     */
    public function __construct()
    {
        $this->competitionSeasonTableItems = new ArrayCollection();
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CompetitionSeason|null
     * @Groups({"table"})
     */
    public function getCompetitionSeason(): ?CompetitionSeason
    {
        return $this->competitionSeason;
    }

    /**
     * @param CompetitionSeason|null $competitionSeason
     * @return CompetitionSeasonTable
     */
    public function setCompetitionSeason(?CompetitionSeason $competitionSeason): self
    {
        $this->competitionSeason = $competitionSeason;

        return $this;
    }

    /**
     * @return int|null
     * @Groups({"table"})
     */
    public function getMatchDay(): ?int
    {
        return $this->match_day;
    }

    /**
     * @param int|null $match_day
     * @return CompetitionSeasonTable
     */
    public function setMatchDay(?int $match_day): self
    {
        $this->match_day = $match_day;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     * @Groups({"table"})
     */
    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTimeInterface|null $timestamp
     * @return CompetitionSeasonTable
     */
    public function setTimestamp(?\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return Collection|CompetitionSeasonTableItem[]
     * @Groups({"table"})
     */
    public function getCompetitionSeasonTableItems(): Collection
    {
        return $this->competitionSeasonTableItems;
    }

    /**
     * @param CompetitionSeasonTableItem $competitionSeasonTableItem
     * @return CompetitionSeasonTable
     */
    public function addCompetitionSeasonTableItem(CompetitionSeasonTableItem $competitionSeasonTableItem): self
    {
        if (!$this->competitionSeasonTableItems->contains($competitionSeasonTableItem)) {
            $this->competitionSeasonTableItems[] = $competitionSeasonTableItem;
            $competitionSeasonTableItem->setCompetitionSeasonTable($this);
        }

        return $this;
    }

    /**
     * @param CompetitionSeasonTableItem $competitionSeasonTableItem
     * @return CompetitionSeasonTable
     */
    public function removeCompetitionSeasonTableItem(CompetitionSeasonTableItem $competitionSeasonTableItem): self
    {
        if ($this->competitionSeasonTableItems->contains($competitionSeasonTableItem)) {
            $this->competitionSeasonTableItems->removeElement($competitionSeasonTableItem);
            // set the owning side to null (unless already changed)
            if ($competitionSeasonTableItem->getCompetitionSeasonTable() === $this) {
                $competitionSeasonTableItem->setCompetitionSeasonTable(null);
            }
        }

        return $this;
    }

    /**
     * @return bool|null
     * @Groups({"table"})
     */
    public function getIsProcessed(): ?bool
    {
        return $this->isProcessed;
    }

    /**
     * @param bool|null $isProcessed
     * @return CompetitionSeasonTable
     */
    public function setIsProcessed(?bool $isProcessed): self
    {
        $this->isProcessed = $isProcessed;

        return $this;
    }

    /**
     * @return string|null
     * @Groups({"table"})
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * @param string|null $groupName
     * @return CompetitionSeasonTable
     */
    public function setGroupName(?string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }
}
