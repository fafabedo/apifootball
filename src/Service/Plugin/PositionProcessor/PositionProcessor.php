<?php

namespace App\Service\Plugin\PositionProcessor;

use App\Entity\PositionMap;
use Doctrine\Common\Persistence\ManagerRegistry;

class PositionProcessor
{
    private $doctrine;

    /**
     * PositionProcessor constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    public function getPositions($top, $left)
    {
        $positions = $this->getDoctrine()
            ->getRepository(PositionMap::class)
            ->findByRelativePosition($top, $left);

    }


}
