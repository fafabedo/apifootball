<?php


namespace App\Tool\TransferMkt;


use App\Entity\CompetitionSeasonTableItem;
use App\Tool\UrlTool;
use Symfony\Component\DomCrawler\Crawler;

class CompetitionTableTool
{
    static public function getTableRows(Crawler $node)
    {
        $rows = $node->filter('div.responsive-table')
            ->filter('table')
            ->filter('tbody')
            ->filter('tr');

        $table = [];
        for($i=0; $i<$rows->count(); $i++) {
            $cellRows = $rows->eq($i)
                ->filter('td');
            $position = $cellRows->eq(0)->text();
            $teamUrl = UrlTool::getHrefAttribute($cellRows->eq(1)->html());
            $tmkCode = UrlTool::getParamFromUrl($teamUrl, 3);
            $matches = $cellRows->eq(2)->text();
            $wins = $cellRows->eq(3)->text();
            $draws = $cellRows->eq(4)->text();
            $loses = $cellRows->eq(5)->text();
            $goals = explode(':', $cellRows->eq(6)->text());
            $diff = $cellRows->eq(7)->text();
            $pts = $cellRows->eq(8)->text();

            $table[] = [
                'position' => $position,
                'team_tmk_code' => $tmkCode,
                'matches' => $matches,
                'wins' => $wins,
                'draws' => $draws,
                'loses' => $loses,
                'gf' => $goals[0] ?? 0,
                'ga' => $goals[1] ?? 0,
                'diff' => $diff,
                'pts' => $pts,

            ];
        }

        return $table;
    }
}
