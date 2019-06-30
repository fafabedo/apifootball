<?php

namespace App\Service\Crawler\Media;

use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;

class MediaCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @return CrawlerInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        return $this;
    }

    /**
     * @return \App\Entity\ProcessQueueMedia[]|object[]
     */
    public function getData()
    {
        return $this
            ->getProcessQueueMediaManager()
            ->getPendingMedia();
    }

    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function saveData(): CrawlerInterface
    {
        $processQueueMedias = $this
            ->getProcessQueueMediaManager()
            ->getPendingMedia();

        foreach ($processQueueMedias as $processQueueMedia) {
            $filename = $processQueueMedia->getFilename();
            $sourceUrl = $processQueueMedia->getSourceUrl();
            try {
                $this
                    ->getMediaManager()
                    ->persistMedia($sourceUrl, $filename);
                $this
                    ->getProcessQueueMediaManager()
                    ->remove($filename);
            }
            catch (\Exception $e) {
                throw new \Exception('Error has occurred persisting media', 0, $e);
            }
        }
        return $this;
    }

}
