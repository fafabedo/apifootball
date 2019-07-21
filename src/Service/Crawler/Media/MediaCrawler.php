<?php

namespace App\Service\Crawler\Media;

use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;

class MediaCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var bool
     */
    private $featured = false;

    /**
     * @return bool
     */
    public function isFeatured(): bool
    {
        return $this->featured;
    }

    /**
     * @param bool $featured
     * @return MediaCrawler
     */
    public function setFeatured(bool $featured): MediaCrawler
    {
        $this->featured = $featured;
        return $this;
    }


    /**
     * @return CrawlerInterface
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function saveData(): CrawlerInterface
    {
        $processQueueMedias = $this
            ->getProcessQueueMediaManager()
            ->getPendingMedia();
        if (empty($processQueueMedias)) {
            $this->setIsCompleted(true);
            return $this;
        }

        $index = 1;
        foreach ($processQueueMedias as $processQueueMedia) {
            if ($index >= $this->getLimit()) {
                break;
            }
            $filename = $processQueueMedia->getFilename();
            $sourceUrl = $processQueueMedia->getSourceUrl();
            try {
                $this
                    ->getMediaManager()
                    ->persistMedia($sourceUrl, $filename);
                $this
                    ->getProcessQueueMediaManager()
                    ->remove($filename);
            } catch (\Exception $e) {
                throw new \Exception('Error has occurred persisting a media resource: '.$filename, 0, $e);
            }
            $index++;
        }
        return $this;
    }

}
