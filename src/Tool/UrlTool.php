<?php


namespace App\Tool;


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
}
