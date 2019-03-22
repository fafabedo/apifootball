<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompetitionSeasonFixtureTeam
 *
 * @ORM\Table(name="competition_season_fixture_team", indexes={@ORM\Index(name="IDX_6830333F296CD8AE", columns={"team_id"}), @ORM\Index(name="IDX_6830333FD5CCA104", columns={"competition_season_fixture_id"})})
 * @ORM\Entity
 */
class CompetitionSeasonFixtureTeam
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
     * @var bool
     *
     * @ORM\Column(name="is_home", type="boolean", nullable=false)
     */
    private $isHome;

    /**
     * @var int|null
     *
     * @ORM\Column(name="final_score", type="integer", nullable=true)
     */
    private $finalScore;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_victory", type="boolean", nullable=true)
     */
    private $isVictory;

    /**
     * @var \CompetitionSeasonFixture
     *
     * @ORM\ManyToOne(targetEntity="CompetitionSeasonFixture")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_season_fixture_id", referencedColumnName="id")
     * })
     */
    private $competitionSeasonFixture;

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
