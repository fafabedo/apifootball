<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * Team
 *
 * @ORM\Table(name="team", indexes={@ORM\Index(name="IDX_C4E0A61F7B39D312", columns={"competition_id"}), @ORM\Index(name="IDX_C4E0A61FF92F3E70", columns={"country_id"})})
 * @ORM\Entity
 */
class Team
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
     * @ORM\Column(name="shortname", type="text", length=0, nullable=true)
     */
    private $shortname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="code", type="string", length=100, nullable=true)
     */
    private $code;

    /**
     * @var array|null
     *
     * @ORM\Column(name="metadata", type="array", length=0, nullable=true)
     */
    private $metadata;

    /**
     * @var \Competition
     *
     * @ORM\ManyToOne(targetEntity="Competition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_id", referencedColumnName="id")
     * })
     */
    private $competition;

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
