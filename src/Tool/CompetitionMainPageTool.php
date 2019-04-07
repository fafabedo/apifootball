<?php

namespace App\Tool;

use Symfony\Component\DomCrawler\Crawler;

class CompetitionMainPageTool
{
    public const TOURNAMENT = 'Tournament';
    public const LEAGUE = 'League';

    /**
     * @param Crawler $node
     * @return string|null
     */
    static public function getNameCompetition(Crawler $node)
    {
        $profileNode = $node->filter('h1.spielername-profil');
        if ($profileNode->count() < 1) {
            return $node->filter('h1')->text();
        }
        return $profileNode->html();
    }

    /**
     * @param Crawler $node
     * @return int|mixed|null
     */
    static public function getLeagueLevel(Crawler $node): ?int
    {
        $tableHtml = $node
            ->filter('table.profilheader');
        if ($tableHtml->count() < 1) {
            return 1;
        }
        $table = $tableHtml->html();

        $stripped = strip_tags(preg_replace('/\s+/', ' ', $table));
        preg_match('/League\sLevel:[\s]+([a-zA-Z|\s]*)/', $stripped, $matches);

        $level = 0;
        if (isset($matches[1])) {
            $level = $matches[1];
        }

        switch ($level) {
            case 'First Tier':
                $level = 1;
                break;
            case 'Second Tier':
                $level = 2;
                break;
            case 'Third Tier':
                $level = 3;
                break;
            case 'Fourth Tier':
                $level = 4;
                break;
            case 'Fifth Tier':
            default:
                $level = 5;
                break;
        }
        return $level;
    }

    /**
     * @param Crawler $node
     * @return int|null
     */
    static public function getNumberTeams(Crawler $node): ?int
    {
        $tableHtml = $node
            ->filter('table.profilheader');
        if ($tableHtml->count() < 1) {
            return 0;
        }
        $table = $tableHtml->html();
        $stripped = strip_tags(preg_replace('/\s+/', ' ', $table));
        preg_match('/Number\sof\steams:[\s]+([0-9]*)/', $stripped, $matches);
        $teams = 0;
        if (isset($matches[1])) {
            $teams = (int)$matches[1];
        }
        return $teams;
    }

    /**
     * @param Crawler $node
     * @return string|null
     */
    static public function getImageFromCompetition(Crawler $node): ?string
    {
        $imageDiv = $node->filterXPath('//div[@class="headerfoto"]');
        if ($imageDiv->count() < 1) {
            $imageDiv = $node->filter('div.dataBild');
        }
        $img = $imageDiv->html();
        preg_match('/src="([^"]+)"/', $img, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @param Crawler $node
     * @return mixed
     */
    static public function getTeamsFromPage(Crawler $node)
    {
        $teams = $node
            ->filterXPath('//*[@id="yw1"]//tbody//tr')
            ->each(function (Crawler $node) {
                $href = $node
                    ->each(function (Crawler $nodeChild, $i) {
                        $filtered = $nodeChild->filter('td')->html();
                        preg_match('/href="([a-zA-Z0-9|_|\/|-]+)"/i', $filtered, $matches);
                        if (!isset($matches[1])) {
                            return '';
                        }
                        return $matches[1];
                    });

                $url = $href[0];
                $names = $node->filterXPath('//a')
                    ->each(function (Crawler $nodeChild, $i) {
                        return $nodeChild->text();
                    });
                $name = [];
                foreach ($names as $text) {
                    if (!empty($text)) {
                        $name[] = $text;
                    }
                    if (count($name) == 2) {
                        break;
                    }
                }
                preg_match('/([^\/]+)\/[^\/]+\/[^\/]+$/', $url, $codeMatches);
                return [
                    'url' => $url,
                    'name' => $name[0],
                    'shortname' => $name[1],
                ];
            });
        return $teams;
    }

    /**
     * @param Crawler $node
     * @return string
     */
    static public function processTypeCompetition(Crawler $node)
    {
        $dataNode = $node->filter('div.dataHeader')
            ->filter('div.dataDaten');
        if ($dataNode->count() < 1) {
            return self::LEAGUE;
        }
        $dataHtml = $dataNode->eq(0)->html();
        if (!preg_match('/Type\sof\scup/', $dataHtml)) {
            return self::LEAGUE;
        }
        return self::TOURNAMENT;
    }
}
