<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * Competition
 *
 * @ORM\Table(name="competition", indexes={@ORM\Index(name="IDX_B50A2CB1DAF94C3D", columns={"competition_type_id"}), @ORM\Index(name="IDX_B50A2CB1F92F3E70", columns={"country_id"}), @ORM\Index(name="IDX_B50A2CB16A03EFC5", columns={"federation_id"})})
 * @ORM\Entity
 */
class Competition
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="league_level", type="integer", nullable=false)
     */
    private $leagueLevel;

    /**
     * @var int
     *
     * @ORM\Column(name="number_teams", type="integer", nullable=false)
     */
    private $numberTeams;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=50, nullable=false)
     */
    private $code;

    /**
     * @var array|null
     *
     * @ORM\Column(name="metadata", type="array", length=0, nullable=true)
     */
    private $metadata;

    /**
     * @var \Federation
     *
     * @ORM\ManyToOne(targetEntity="Federation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="federation_id", referencedColumnName="id")
     * })
     */
    private $federation;

    /**
     * @var \CompetitionType
     *
     * @ORM\ManyToOne(targetEntity="CompetitionType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_type_id", referencedColumnName="id")
     * })
     */
    private $competitionType;

    /**
     * @var \Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     * })
     */
    private $country;


}
