<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompetitionSeasonPlayer
 *
 * @ORM\Table(name="competition_season_player", indexes={@ORM\Index(name="IDX_79B74677DA3407DA", columns={"competition_season_team_id"})})
 * @ORM\Entity
 */
class CompetitionSeasonPlayer
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
     * @var \Player
     *
     * @ORM\ManyToOne(targetEntity="Player")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_season_team_id", referencedColumnName="id")
     * })
     */
    private $competitionSeasonTeam;


}
