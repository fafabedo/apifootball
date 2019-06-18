<?php

namespace App\Service\Crawler;

use App\Entity\CachePage;

/**
 * Class CachePageClearCrawler
 * @package App\Service\Crawler
 */
class CachePageClearCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @return CrawlerInterface
     */
    public function process(): CrawlerInterface
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this
            ->getDoctrine()
            ->getRepository(CachePage::class)
            ->findAllCacheExpired();
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        $this
            ->getDoctrine()
            ->getRepository(CachePage::class)
            ->deleteAllCacheExpired();
        return $this;
    }

}
