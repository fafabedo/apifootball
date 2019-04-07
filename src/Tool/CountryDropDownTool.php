<?php


namespace App\Tool;


use Symfony\Component\DomCrawler\Crawler;

class CountryDropDownTool
{
    /**
     * Get code and names from dropdown https://www.transfermarkt.co.uk/site/dropDownLaender
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
