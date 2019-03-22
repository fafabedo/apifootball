<?php

namespace App\ImportSchema;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompetitionType
 *
 * @ORM\Table(name="competition_type")
 * @ORM\Entity
 */
class CompetitionType
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


}
