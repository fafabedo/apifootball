<?php


namespace App\Tool;


use App\Entity\TeamType;
use Doctrine\Common\Persistence\ManagerRegistry;

class TypeTool
{
    const National_Team = 'National';

    const Club_Team = 'Club';

    /**
     * @param ManagerRegistry $doctrine
     * @return TeamType
     */
    static public function getNationalTypeTeam(ManagerRegistry $doctrine): TeamType
    {
        return $doctrine
            ->getRepository(TeamType::class)
            ->findOneBy(['name' => self::National_Team]);

    }

    /**
     * @param ManagerRegistry $doctrine
     * @return TeamType
     */
    static public function getClubTypeTeam(ManagerRegistry $doctrine): TeamType
    {
        return $doctrine
            ->getRepository(TeamType::class)
            ->findOneBy(['name' => self::Club_Team]);

    }

}
