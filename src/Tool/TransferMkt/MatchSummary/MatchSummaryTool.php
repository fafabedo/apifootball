<?php


namespace App\Tool\TransferMkt\MatchSummary;


use App\Service\Crawler\Entity\Match\MatchLineupPlayer;
use App\Tool\HtmlTool;
use App\Tool\UrlTool;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class MatchSummaryTool
 * @package App\Tool\TransferMkt\MatchSummary
 */
class MatchSummaryTool
{
    /**
     * @param Crawler $node
     */
    static public function getMatchSummaryInformation(Crawler $node)
    {

    }

    /**
     * @param Crawler $node
     * @return array
     */
    static public function getStadiumArray(Crawler $node)
    {
        $wrapper = $node->filter('.sb-zusatzinfos')
            ->html();
        $stadiumName = $node
            ->filter('.sb-zusatzinfos')
            ->filter('span')
            ->text();
        $path = '';
        if (preg_match('/<a.*href="([^"]+)"/', $wrapper, $matches)) {
            $path = $matches[1];
        }
        return [$stadiumName => $path];
    }

    /**
     * @param Crawler $node
     * @return array
     */
    static public function getStartingTeamPositions(Crawler $node)
    {
        $teams = $node->filter('.aufstellung-box')
            ->parents()
            ->filter('.large-6');
        $lineup = [];
        $i = 0;
        foreach ($teams as $team) {
            $class = $team->getAttribute('class');
            if (!preg_match('/large-6/', $class) || !preg_match('/columns/', $class)) {
                continue;
            }
            $teamNode = new Crawler();
            $teamNode->addNode($team);
            $divs = $teamNode->filter('.aufstellung-spieler-container');
            foreach ($divs as $div) {
                $style = $div->getAttribute('style');
                $top = $left = 0;
                if (preg_match('/top:([^:;%]+)/i', $style, $matches)) {
                    $top = $matches[1];
                }
                if (preg_match('/left:([^:;%]+)/i', $style, $matches)) {
                    $left = $matches[1];
                }
                $divNode = new Crawler();
                $divNode->addNode($div);
                $tmp = $divNode->html();
                $items = $divNode->children();
                $tmkCode = $items->eq(0)->attr('id');
                $number = $items->eq(0)->text();
                $lineupPlayer = new MatchLineupPlayer();
                $lineupPlayer->setCode($tmkCode);
                $lineupPlayer->setNumber($number);
                $lineupPlayer->setTop($top);
                $lineupPlayer->setLeft($left);
                $lineup[$i][] = $lineupPlayer;
            }
            $i++;
        }
        return $lineup;
    }

    /**
     * @param Crawler $node
     * @return array
     */
    static public function getSubs(Crawler $node)
    {
        $subs = [];
        $largeDivs = $node->filter('.large-6');
        $i = 1;
        while ($i < 3) {
            $mainWrapper = $largeDivs->eq($i)
                ->filter('.large-5')
                ->eq(2)
                ->filter('tr')
                ;
            /* @var \DOMElement $trNode */
            foreach ($mainWrapper as $trNode) {
                $childNode = new Crawler();
                $childNode->addNode($trNode);
                $childTd = $childNode->filter('td');
                $tdHtml = $childNode->html();
                if (!preg_match('/nummer/', $tdHtml)) {
                    continue;
                }
                $number = $childTd->eq(0)->text();
                $number = HtmlTool::trimHtml($number);
                $playerHtml = $childTd->eq(1)->html();
                if (!preg_match('/href="([^"]*)"/', $playerHtml, $matches)) {
                    continue;
                }
                $tmkCode = UrlTool::getParamFromUrl($matches[1], 3);
                $lineupPlayer = new MatchLineupPlayer();
                $lineupPlayer->setNumber($number);
                $lineupPlayer->setCode($tmkCode);
                $subs[$i - 1][] = $lineupPlayer;
            }
            $i++;
        }
        return $subs;
    }
}
