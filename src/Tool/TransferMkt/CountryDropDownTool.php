<?php

namespace App\Tool\TransferMkt;

use Symfony\Component\DomCrawler\Crawler;

class CountryDropDownTool
{
    /**
     * Get code and names from dropdown /site/dropDownLaender
     * @param Crawler $node
     * @return array/null
     */
    static public function getCodesAndNamesFromDropDown(Crawler $node): array
    {
        $content = $node->html();
        preg_match_all('/value="([0-9]+)">([^"]+)<\//', $content, $matches);
        $countryKeys = $matches[1] ?? [];
        $countryNames = $matches[2] ?? [];

        if (count($countryKeys) !== count($countryNames)) {
            return null;
        }

        return [
            'keys' => $countryKeys,
            'names' => $countryNames
        ];
    }
}
