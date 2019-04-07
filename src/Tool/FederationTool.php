<?php


namespace App\Tool;


use App\Entity\Federation;
use Doctrine\Common\Persistence\ManagerRegistry;

class FederationTool
{
    public const FIFA_FEDERATION = 'FIFA';

    public const UEFA_FEDERATION = 'UEFA';
    public const CONMEBOL_FEDERATION = 'CONMEBOL';
    public const CONCACAF_FEDERATION = 'CONCACAF';
    public const AFC_FEDERATION = 'AFC';
    public const CAF_FEDERATION = 'CAF';
    public const OFC_FEDERATION = 'OFC';

    /**
     * @param ManagerRegistry $doctrine
     * @return Federation
     */
    static public function getUefaFederation(ManagerRegistry $doctrine): Federation
    {
        return $doctrine
            ->getRepository(Federation::class)
            ->findOneBy(['shortname' => self::UEFA_FEDERATION]);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @return Federation
     */
    static public function getConmebolFederation(ManagerRegistry $doctrine): Federation
    {
        return $doctrine
            ->getRepository(Federation::class)
            ->findOneBy(['shortname' => self::CONMEBOL_FEDERATION]);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @return Federation
     */
    static public function getFifaFederation(ManagerRegistry $doctrine): Federation
    {
        return $doctrine
            ->getRepository(Federation::class)
            ->findOneBy(['shortname' => self::FIFA_FEDERATION]);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @return Federation
     */
    static public function getConcacafFederation(ManagerRegistry $doctrine): Federation
    {
        return $doctrine
            ->getRepository(Federation::class)
            ->findOneBy(['shortname' => self::CONCACAF_FEDERATION]);
    }
}
