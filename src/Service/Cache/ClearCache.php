<?php


namespace App\Service\Cache;


use App\Entity\CachePage;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class ClearCache
 * @package App\Service\Cache
 */
class ClearCache
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * ClearCache constructor.
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

    public function clearCacheExpired(): ClearCache
    {
        $deleted = $this
            ->getDoctrine()
            ->getRepository(CachePage::class)
            ->findAllCacheExpired()
        ;
        return $this;
    }
}
