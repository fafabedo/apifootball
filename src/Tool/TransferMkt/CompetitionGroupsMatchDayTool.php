<?php


namespace App\Tool\TransferMkt;


use Symfony\Component\DomCrawler\Crawler;

class CompetitionGroupsMatchDayTool
{
    const MATCH_STAGE_FINAL = 'Final';
    const MATCH_STAGE_THIRD_PLACE = 'Third Place';
    const MATCH_STAGE_SEMI_FINALS = 'Semi-Finals';
    const MATCH_STAGE_QUARTER_FINALS = 'Quarter-Finals';
    const MATCH_STAGE_ROUND_16 = 'Round of 16';
    const MATCH_STAGE_ROUND_32 = 'Round of 32';
    const MATCH_STAGE_PLAYOFF = 'Playoff';
    const MATCH_STAGE_GROUP = 'Group';

    /**
     * @param Crawler $node
     * @return string
     */
    static public function getGroupNameFromTable(Crawler $node)
    {
        return $node
            ->filter('div.table-header')
            ->text();
    }

    /**
     * @param Crawler $node
     * @return Crawler
     */
    static public function getTournamentBoxNodes(Crawler $node)
    {
        return $node
            ->filterXPath('//div[@id="main"]')
            ->filter('div.box');
    }

    /**
     * @param Crawler $node
     * @return string/null
     */
    static public function getTypeTournamentBox(Crawler $node)
    {
        if (preg_match('/knockout/i', $node->html())) {
            return 'knockout';
        }
        if (preg_match('/group/i', $node->html())) {
            return 'group';
        }
        return null;
    }

    /**
     * @param Crawler $node
     * @return array
     */
    static public function getGroupRowsMatches(Crawler $node)
    {
        $tableNodes = $node->filter('table')
            ->each(function (Crawler $table, $i) {
                if ($i === 0) {
                    return null;
                }
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
        $finalRows = [];
        foreach ($tableNodes as $item) {
            if ($item !== null) {
                $finalRows = $item;
            }
        }
        return $finalRows;
    }

    /**
     * @param Crawler $node
     * @return array
     */
    static public function getKnockoutRowsMatches(Crawler $node)
    {
        $tableNodes = $node->filter('table')
            ->each(function (Crawler $table) {
                return $table
                    ->filter('tbody')
                    ->filter('tr')->each(function (Crawler $tableRow) {
                        if ($tableRow->attr('class') === 'bg_blau_20') {
                            return null;
                        }
                        return $tableRow;
                    });
            });
        $rounds = [];
        $section = self::MATCH_STAGE_PLAYOFF;
        foreach ($tableNodes[0] as $rowNode) {
            if (!$rowNode instanceof Crawler) {
                continue;
            }
            $class = $rowNode->attr('class');
            if (preg_match('/bg_sturm/i', $class)) {
                $sectionText = $rowNode->text();
                $section = self::getSectionProceed($sectionText);
                continue;
            }

            $rounds[$section][] = $rowNode;

        }
        return $rounds;
    }

    /**
     * @param $sectionText
     * @return string
     */
    static public function getSectionProceed($sectionText)
    {
        if (preg_match('/third/i', $sectionText)) {
            return self::MATCH_STAGE_THIRD_PLACE;
        }

        if (preg_match('/semi/i', $sectionText)) {
            return self::MATCH_STAGE_SEMI_FINALS;
        }

        if (preg_match('/quarter/i', $sectionText)) {
            return self::MATCH_STAGE_QUARTER_FINALS;
        }

        if (preg_match('/16/i', $sectionText)) {
            return self::MATCH_STAGE_ROUND_16;
        }

        if (preg_match('/intermediate/i', $sectionText)) {
            return self::MATCH_STAGE_ROUND_32;
        }

        if (preg_match('/32/i', $sectionText)) {
            return self::MATCH_STAGE_ROUND_32;
        }
        if (preg_match('/final/i', $sectionText)) {
            return self::MATCH_STAGE_FINAL;
        }
        return self::MATCH_STAGE_PLAYOFF;
    }
}
