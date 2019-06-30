<?php

namespace App\Tool\TransferMkt;

use App\Service\Crawler\Item\EntityElement;
use App\Tool\HtmlTool;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CompetitionEuropeTool
 * @package App\Tool
 */
class CompetitionEuropeTool
{
    /**
     * @param Crawler $node
     * @return int
     */
    static public function getParticipants(Crawler $node): int
    {
        $dataNodes = $node
            ->filterXPath('//div[@class="dataContent"]//div[@class="dataDaten"]')
            ->filter('span.dataValue')
            ->each(function(Crawler $node) {
                return $node;
            })
        ;
        $teams = 0;
        if (isseT($dataNodes[1]) && $dataNodes[1] instanceof Crawler) {
            $teams = (int)$dataNodes[1]->text();
        }
        return $teams;

    }

    /**
     * @param Crawler $node
     * @return string|null
     */
    static public function getImageFromCompetition(Crawler $node): ?string
    {
        $bindHtml = $node
            ->filter('div.dataBild');
        if ($bindHtml->count() < 1) {
            return null;
        }
        $image = $bindHtml->html();
        preg_match('/src="([^"]+)"/', $image, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @param Crawler $node
     * @return Crawler
     */
    static public function getRightColumnHtml(Crawler $node)
    {
        return $node->filter('div.large-4');
    }

    /**
     * @param Crawler $node
     * @param $pattern
     * @return array
     */
    static public function getCompetitionsByPattern(Crawler $node, $pattern): array
    {
        $column = self::getRightColumnHtml($node);
        $rows = $column
            ->filterXPath('//div[@class="box"]')
            ->each(function(Crawler $node) use($pattern) {
                $boxHtml = $node->html();
                if (!preg_match($pattern, $boxHtml)) {
                    return null;
                }
                return $node;
            });
        $comps = [];
        foreach ($rows as $node) {
            if (!$node instanceof Crawler) {
                continue;
            }
            $comps = $node->filter('li')->each(function(Crawler $node) {
                $liHtml = $node->html();
                preg_match('/href="([^\"]+)"/', $liHtml, $matches);
                $name = HtmlTool::trimHtml($node->text());
                $competition = null;
                if (isset($matches[1])) {
                    $competition = new EntityElement();
                    $competition->setUrl($matches[1]);
                    $competition->setName(HtmlTool::trimHtml($name));
                }
                return $competition;
            });
        }
        return $comps;
    }

    /**
     * @param Crawler $node
     * @return array
     */
    static public function getClubCompetitions(Crawler $node): array
    {
        return self::getCompetitionsByPattern($node, '/Cup\scompetitions/');
    }

    /**
     * @param Crawler $node
     * @return array
     */
    static public function getNationalCompetitions(Crawler $node): array
    {
        return self::getCompetitionsByPattern($node, '/International\sCups/');
    }
}
