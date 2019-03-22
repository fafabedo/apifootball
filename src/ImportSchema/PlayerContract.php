<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlayerContract
 *
 * @ORM\Table(name="player_contract", indexes={@ORM\Index(name="IDX_EF5869B9296CD8AE", columns={"team_id"}), @ORM\Index(name="IDX_EF5869B999E6F5DF", columns={"player_id"})})
 * @ORM\Entity
 */
class PlayerContract
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
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_at", type="date", nullable=true)
     */
    private $startAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_at", type="date", nullable=true)
     */
    private $endAt;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="archive", type="boolean", nullable=true)
     */
    private $archive;

    /**
     * @var string|null
     *
     * @ORM\Column(name="annual_salary", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $annualSalary;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="joined_to_team_at", type="date", nullable=true)
     */
    private $joinedToTeamAt;

    /**
     * @var \Team
     *
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     * })
     */
    private $team;

    /**
     * @var \Player
     *
     * @ORM\ManyToOne(targetEntity="Player")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     * })
     */
    private $player;


}
