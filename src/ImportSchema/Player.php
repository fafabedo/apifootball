<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player", indexes={@ORM\Index(name="IDX_98197A653414710B", columns={"agent_id"})})
 * @ORM\Entity
 */
class Player
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
     * @ORM\Column(name="name", type="text", length=0, nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="shortname", type="string", length=50, nullable=true)
     */
    private $shortname;

    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="guid", length=36, nullable=false, options={"fixed"=true})
     */
    private $uuid;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var string|null
     *
     * @ORM\Column(name="place_of_birth", type="text", length=0, nullable=true)
     */
    private $placeOfBirth;

    /**
     * @var float|null
     *
     * @ORM\Column(name="height", type="float", precision=10, scale=0, nullable=true)
     */
    private $height;

    /**
     * @var string|null
     *
     * @ORM\Column(name="code", type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column(name="picture", type="text", length=0, nullable=true)
     */
    private $picture;

    /**
     * @var string|null
     *
     * @ORM\Column(name="foot", type="string", length=15, nullable=true)
     */
    private $foot;

    /**
     * @var string|null
     *
     * @ORM\Column(name="outfitter", type="string", length=100, nullable=true)
     */
    private $outfitter;

    /**
     * @var \Agent
     *
     * @ORM\ManyToOne(targetEntity="Agent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    private $agent;


}
