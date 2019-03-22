<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlayerPosition
 *
 * @ORM\Table(name="player_position", indexes={@ORM\Index(name="IDX_40FBA515DD842E46", columns={"position_id"}), @ORM\Index(name="IDX_40FBA51599E6F5DF", columns={"player_id"})})
 * @ORM\Entity
 */
class PlayerPosition
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
     *   @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     * })
     */
    private $player;

    /**
     * @var \Position
     *
     * @ORM\ManyToOne(targetEntity="Position")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="position_id", referencedColumnName="id")
     * })
     */
    private $position;


}
