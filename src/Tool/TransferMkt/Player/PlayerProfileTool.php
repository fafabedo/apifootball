<?php


namespace App\Tool\TransferMkt\Player;


use App\Tool\HtmlTool;
use Symfony\Component\DomCrawler\Crawler;

class PlayerProfileTool
{
    /**
     * @param Crawler $crawler
     * @return string
     */
    public static function getName(Crawler $crawler)
    {
        try {
            return $crawler->filter('h1')
                ->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
     */
    public static function getJerseyNumber(Crawler $crawler)
    {
        try {
            $node = $crawler->filter('h1')
                ->parents()
                ->filter('.dataRN');
            if (preg_match('/([0-9]+)/', $node->text(), $matches)) {
                return $matches[1];
            }
            return '0';

        } catch (\Exception $e) {
            return '0';
        }
    }

    /**
     * @param Crawler $crawler
     * @return mixed|null
     */
    public static function getProfilePictureFilename(Crawler $crawler)
    {
        try {
            $nodeHtml = $crawler->filter('.dataHeader')
                ->filter('.dataBild')
                ->html();
            if (preg_match('/<img.+src="([^"]+)"/i', $nodeHtml, $matches)) {
                return $matches[1];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param Crawler $crawler
     * @return null
     */
    public static function getContainerProfile(Crawler $crawler)
    {
        $node = $crawler->filter('.spielerdatenundfakten');
        $node = $node->filter('table.auflistung');

        return $node;
    }

    /**
     * @param Crawler $crawler
     * @return string
     */
    public static function getFullName(Crawler $crawler)
    {
        $rows = $crawler->filter('tr');
        foreach ($rows as $row) {
            $nodeRow = new Crawler();
            $nodeRow->addNode($row);
            $label = $nodeRow->filter('th')->text();
            if (preg_match('/full.name/i', $label)) {
                return $nodeRow->filter('td')->text();
            }
        }

        return '';
    }

    /**
     * @param Crawler $crawler
     * @return string
     */
    public static function getBirthday(Crawler $crawler)
    {
        $rows = $crawler->filter('tr');
        foreach ($rows as $row) {
            $nodeRow = new Crawler();
            $nodeRow->addNode($row);
            $label = $nodeRow->filter('th')->text();
            if (preg_match('/date.+birth/i', $label)) {
                return HtmlTool::trimHtml($nodeRow->filter('td')->text());
            }
        }

        return '';
    }

    /**
     * @param Crawler $crawler
     * @return string
     */
    public static function getPlaceBirth(Crawler $crawler)
    {
        $rows = $crawler->filter('tr');
        foreach ($rows as $row) {
            $nodeRow = new Crawler();
            $nodeRow->addNode($row);
            $label = $nodeRow->filter('th')->text();
            if (preg_match('/place.+birth/i', $label)) {
                $field = $nodeRow->filter('td')->html();
                if (preg_match('/<span[^>]+>([^<]+).+title="([^"]+)"/i', $field, $matches)) {
                    $matches[1] = HtmlTool::trimHtml($matches[1]);
                    $matches[2] = HtmlTool::trimHtml($matches[2]);
                    return HtmlTool::trimHtml(implode(', ', [$matches[1], $matches[2]]));
                }

                return HtmlTool::trimHtml($nodeRow->filter('td')->text());
            }
        }

        return '';
    }

    /**
     * @param Crawler $crawler
     * @return string
     */
    public static function getHeight(Crawler $crawler)
    {
        $rows = $crawler->filter('tr');
        foreach ($rows as $row) {
            $nodeRow = new Crawler();
            $nodeRow->addNode($row);
            $label = $nodeRow->filter('th')->text();
            if (preg_match('/height/i', $label)) {
                $text = HtmlTool::trimHtml($nodeRow->filter('td')->text());
                $numberText = preg_replace(['/,/','/m/'], ['.',''], $text);
                return (float) $numberText;
            }
        }

        return (float) 0;
    }

    /**
     * @param Crawler $crawler
     * @return string
     */
    public static function getFoot(Crawler $crawler)
    {
        $rows = $crawler->filter('tr');
        foreach ($rows as $row) {
            $nodeRow = new Crawler();
            $nodeRow->addNode($row);
            $label = $nodeRow->filter('th')->text();
            if (preg_match('/foot/i', $label)) {
                return HtmlTool::trimHtml($nodeRow->filter('td')->text());
            }
        }

        return '';
    }
}
