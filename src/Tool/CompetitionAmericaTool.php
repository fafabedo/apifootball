<?php

namespace App\Tool;

use Doctrine\Common\Persistence\ManagerRegistry;

class CompetitionAmericaTool
{
    /**
     * Determine federation
     * @param ManagerRegistry $doctrine
     * @param $name
     * @return \App\Entity\Federation
     */
    static public function determineFederation(ManagerRegistry $doctrine, $name)
    {
        if (preg_match('/\samérica/i', $name)) {
            return FederationTool::getConmebolFederation($doctrine);
        }
        if (preg_match('/\sam.rica|\slibertadores|\ssudamericana|\ssouth/i', $name, $matches)) {
            return FederationTool::getConmebolFederation($doctrine);
        }
        return FederationTool::getConcacafFederation($doctrine);
    }

}
