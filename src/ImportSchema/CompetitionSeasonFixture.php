<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompetitionSeasonFixture
 *
 * @ORM\Table(name="competition_season_fixture", indexes={@ORM\Index(name="IDX_F6BD9F9AE5204DB3", columns={"competition_season_id"})})
 * @ORM\Entity
 */
class CompetitionSeasonFixture
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
     * @var \DateTime
     *
     * @ORM\Column(name="match_date", type="date", nullable=false)
     */
    private $matchDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="match_time", type="datetime", nullable=true)
     */
    private $matchTime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="attendance", type="integer", nullable=true)
     */
    private $attendance;

    /**
     * @var \CompetitionSeason
     *
     * @ORM\ManyToOne(targetEntity="CompetitionSeason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_season_id", referencedColumnName="id")
     * })
     */
    private $competitionSeason;


}
