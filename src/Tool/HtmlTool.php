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
        $content = static::replaceSpecialCharacters($content);
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

    static public function replaceSpecialCharacters($content)
    {
        $characters = [
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
        ];
        return preg_replace(array_keys($characters), array_values($characters), $content);
    }
}
