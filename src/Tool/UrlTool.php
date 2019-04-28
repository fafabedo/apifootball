<?php


namespace App\Tool;


use Symfony\Component\DomCrawler\Crawler;

class UrlTool
{
    /**
     * @param $url
     * @param $position
     * @return |null
     */
    static public function getParamFromUrl($url, $position)
    {
        $result = null;
        preg_match_all('/\/([a-z0-9-|_|.]+)/i', $url, $matches);
        if (isset($matches[1]) && isset($matches[1][$position])) {
            $result = $matches[1][$position];
        }
        return $result;
    }

    /**
     * @param $htmlText
     * @return string|null
     */
    static public function getHrefAttribute($htmlText): ?string
    {
        if (preg_match('/href="([a-zA-Z0-9\%|_|\/|-]+)"/', $htmlText, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
