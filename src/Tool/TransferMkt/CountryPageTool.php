<?php

namespace App\Tool\TransferMkt;

use App\Service\Crawler\Item\EntityElement;
use Symfony\Component\DomCrawler\Crawler;

class CountryPageTool
{
    /**
     * Crawl national teams template /wettbewerbe/national/wettbewerbe/9
     * @param Crawler $node
     * @return array
     */
    static public function getNationalTeamLinks(Crawler $node)
    {
        $wrapper = $node->filter('div.relevante-wettbewerbe-auflistung');
        $list = $wrapper->filter('li')->each(function(Crawler $node) {
            $item = $node->html();
            preg_match('/href="([a-z0-9-|\/]+)"/', $item, $urlMatches);
            $url = null;
            if (isset($urlMatches[1])) {
                $url = $urlMatches[1];
            }
            $title = trim($node->children()->text());
            if (!empty($url)) {
                $entityElement = new EntityElement();
                $entityElement->setUrl($url);
                $entityElement->setName(trim($title, chr(0xC2).chr(0xA0)));
                return $entityElement;
            }
        });
        return $list;
    }

    /**
     * Template Country Page: /argentina/startseite/verein/?1
     * @param Crawler $node
     * @return string
     */
    static public function getFederationFromCountryPage(Crawler $node)
    {
        $wrapper = $node
            ->filter('div.nationalmannschaft')
            ->filter('div.dataContent')
            ->filter('div.dataDaten')
        ;
        $federation = $wrapper->each(function(Crawler $node){
            $content = $node->html();
            preg_match('/(Confederation:).*\n.*dataValue">([a-zA-Z]+)<\/span>/', $content, $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                return $matches[2];
            }
        });
        if (!isset($federation[1])) {
            return '';
        }
        return $federation[1];

    }
}
