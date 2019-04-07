<?php

namespace App\Tool;

use App\Entity\CompetitionSeasonMatch;
use App\Service\Metadata\MetadataSchemaResources;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CompetitionFixtureTool
 * @package App\Tool
 */
class CompetitionFixtureTool
{
    /**
     * @param Crawler $node
     * @return Crawler
     */
    static public function getFixtureTables(Crawler $node): Crawler
    {
        return $node
            ->filter('div.large-6')
            ->filter('table');
    }

    /**
     * @param Crawler $node
     * @return array
     */
    static public function getTableNodes(Crawler $node)
    {
        $tableNodes = self::getFixtureTables($node);
        $cleanTableNodes = $tableNodes->each(function (Crawler $table, $i) {
            $rows = $table
                ->filter('tbody')
                ->filter('tr')->each(function (Crawler $tableRow) {
                    $html = $tableRow->html();
                    if (preg_match('/colspan="."/i', $html)) {
                        return null;
                    }
                    return $tableRow;

                });
            $realRows = [];
            foreach ($rows as $row) {
                if ($row instanceof Crawler) {
                    $realRows[] = $row;
                }
            }
            return $realRows;
        });
        return $cleanTableNodes;
    }

    /**
     * @param string $cellDate
     * @return \DateTime
     * @throws \Exception
     */
    static public function extractDateCellDate($cellDate): ?\DateTime
    {
        if (!preg_match('/(....\-..\-..)/', $cellDate, $matches)) {
            return null;
        }
        $dateText = $matches[1];
        return DateTimeTool::createDateTime($dateText);
    }

    /**
     * @param $cellLink
     * @return string
     */
    static public function extractMatchLink($cellLink): ?string
    {
        if (preg_match('/href="([^"]+)">/i', $cellLink, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @param $html
     * @return |null
     */
    static public function extractTeamCode($html)
    {
        if (!preg_match('/href="([^"]+)">/', $html, $matches)) {
            return null;
        }
        if (isset($matches[1])) {
            $path = $matches[1];
            return UrlTool::getParamFromUrl($path, 3);
        }
        return null;
    }

    /**
     * @param $html
     * @param bool $home
     * @return int|null
     */
    static public function extractScore($html, $home = true): ?int
    {
        if (preg_match('/>([0-9]{1,2}):([0-9]{1,2})</', $html, $matches)) {
            if ($home) {
                return (int) $matches[1];
            }
            return (int) $matches[2];
        }
        return null;
    }
}
