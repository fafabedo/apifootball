<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompetitionSeasonTeam
 *
 * @ORM\Table(name="competition_season_team", indexes={@ORM\Index(name="IDX_6A0895FF296CD8AE", columns={"team_id"}), @ORM\Index(name="IDX_6A0895FFE5204DB3", columns={"competition_season_id"})})
 * @ORM\Entity
 */
class CompetitionSeasonTeam
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \CompetitionSeason
     *
     * @ORM\ManyToOne(targetEntity="CompetitionSeason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_season_id", referencedColumnName="id")
     * })
     */
    private $competitionSeason;

    /**
     * @var \Team
     *
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     * })
     */
    private $team;


}
