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
        return [1];
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

        $this->setIsCompleted(true);

        return $this;
    }

}
