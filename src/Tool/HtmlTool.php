<?php


namespace App\Tool;


use Symfony\Component\DomCrawler\Crawler;

/**
 * Class HtmlTool
 * @package App\Tool
 */
class HtmlTool
{
    /**
     * Trim HTML spaces and special characters
     * @param $content
     * @return mixed|string
     */
    static public function trimHtml($content){
        $content = str_replace('\"', '"', $content);
        $content = urldecode($content);
        $content = trim($content);
        $content = trim($content, chr(0xC2).chr(0xA0));
        return $content;
    }

    /**
     * Get Canonical Href from HTML
     * @param Crawler $node
     * @return string
     */
    static public function getCanonical(Crawler $node): string
    {
        $html = $node->html();
        preg_match('/canonical"[\s]+href="([^"]+)"/', $html, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}
