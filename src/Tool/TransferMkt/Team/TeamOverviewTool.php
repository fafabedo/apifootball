<?php


namespace App\Tool\TransferMkt\Team;


use App\Tool\HtmlTool;
use Symfony\Component\DomCrawler\Crawler;

class TeamOverviewTool
{
    /**
     * @param Crawler $node
     * @return string
     */
    static public function getFullName(Crawler $node)
    {
        $title = $node
            ->filter('h1')
            ->text();
        return HtmlTool::trimHtml($title);
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

    /**
     * @param Crawler $node
     * @return mixed|null
     */
    static public function getTeamImage(Crawler $node)
    {
        $imageTag = $node->filter('div.dataHeader')
            ->filter('.dataBild')
            ->html();
        if (preg_match('/<img.+src="([^"]+)"/', $imageTag, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @param Crawler $node
     * @return mixed|null
     */
    static public function getCanonical(Crawler $node)
    {
        if (preg_match('/<link.+"canonical".+href="([^"]+)"/', $node->html(), $matches)) {
            return $matches[1];
        }
        return null;
    }
}
