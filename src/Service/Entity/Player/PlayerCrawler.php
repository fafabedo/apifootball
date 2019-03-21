<?php


namespace App\Service\Entity\Player;


use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;

class PlayerCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @return string
     */
    public function process(): CrawlerInterface
    {
        $content = $this->getCrawler()->getContent();
        $content = str_replace('\\"','"',$content);

        preg_match_all('/"([0-9]+)"\>([a-zA-Z]+)/i', $content, $matches);

        $html = '';
        foreach ($matches[1] as $key => $id) {
            $name = $matches[2][$key];
            $html .= "$id:$name|";

        }

        return $this;
    }

    public function getData()
    {
        // TODO: Implement getData() method.
    }


    public function saveData():CrawlerInterface
    {
        // TODO: Implement saveEntities() method.
        return $this;
    }
}