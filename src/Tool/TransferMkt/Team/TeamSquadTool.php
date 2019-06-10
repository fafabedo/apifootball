<?php

namespace App\Tool\TransferMkt\Team;

use Symfony\Component\DomCrawler\Crawler;

class TeamSquadTool
{
    /**
     * @param Crawler $crawler
     * @return array
     */
    public static function getPlayersRows(Crawler $crawler)
    {
        $resultTable = [];
        $table = $crawler->filter('table.items');
        $tableRows = $table->filter('tbody > tr');
        foreach ($tableRows as $index => $tableRow) {
            $row = new Crawler();
            $row->addNode($tableRow);
            $rowCells = $row->filter('td');
            $info = $rowCells->eq(1)->html();
            $resultTable[$index] = [
                'tmkCode' => self::getPlayerId($info),
                'name' => self::getPlayerName($info),
                'url' => self::getPlayerUrl($info),
            ];

        }
        return $resultTable;
    }

    public static function getPlayerName($htmlTable)
    {
        if (preg_match('/<a.title="([^"]+)".*id="([0-9]+)".+href="([^"]+)"/i', $htmlTable, $matches)) {
            return $matches[1];
        }
    }

    public static function getPlayerId($htmlTable)
    {
        if (preg_match('/<a.title="([^"]+)".*id="([0-9]+)".+href="([^"]+)"/i', $htmlTable, $matches)) {
            return $matches[2];
        }
    }

    public static function getPlayerUrl($htmlTable)
    {
        if (preg_match('/<a.title="([^"]+)".*id="([0-9]+)".+href="([^"]+)"/i', $htmlTable, $matches)) {
            return $matches[3];
        }
    }
}
