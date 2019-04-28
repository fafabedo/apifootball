<?php


namespace App\Tool\TransferMkt\Team;


use Symfony\Component\DomCrawler\Crawler;

class TeamOverviewTool
{
    /**
     * @param Crawler $node
     * @return string
     */
    static public function getFullName(Crawler $node)
    {
        return $node
            ->filter('h1.name')
            ->text();
    }

    /**
     * @param Crawler $node
     * @return mixed|null
     */
    static public function getCompetitionLink(Crawler $node)
    {
        $competitionLink = $node
            ->filter('span.hauptpunkt')
            ->html();
        if (preg_match('/href="([^"]+)"/', $competitionLink, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
