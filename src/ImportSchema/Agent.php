<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * Agent
 *
 * @ORM\Table(name="agent")
 * @ORM\Entity
 */
class Agent
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
     * @var string|null
     *
     * @ORM\Column(name="metadata", type="blob", length=0, nullable=true)
     */
    private $metadata;


}
