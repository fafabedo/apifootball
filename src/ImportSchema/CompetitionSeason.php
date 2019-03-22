<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompetitionSeason
 *
 * @ORM\Table(name="competition_season", indexes={@ORM\Index(name="IDX_9FA79B7A7B39D312", columns={"competition_id"})})
 * @ORM\Entity
 */
class CompetitionSeason
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
     * @ORM\Column(name="start_season", type="date", nullable=false)
     */
    private $startSeason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_season", type="date", nullable=false)
     */
    private $endSeason;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="archive", type="boolean", nullable=true)
     */
    private $archive;

    /**
     * @var \Competition
     *
     * @ORM\ManyToOne(targetEntity="Competition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_id", referencedColumnName="id")
     * })
     */
    private $competition;


}
