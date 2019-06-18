<?php

namespace App\Tool\FifaSite;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class FifaSiteFederationTool
 * @package App\Tool\FifaSite
 */
class FifaSiteFederationTool
{
    /**
     * @param Crawler $node
     * @param array $federationCountries
     * @return array
     */
    static public function getFederationCountries(Crawler $node, $federationCountries = [])
    {
        $names = $node->filter('span.fi-a__nText')->each(function (Crawler $node) {
            return $node->text();
        });
        $codes = $node->filter('span.fi-a__nTri')->each(function (Crawler $node) {
            return $node->text();
        });
        foreach ($names as $key => $countryName) {
            $federationCountries[$codes[$key]] = $countryName;
        }
        return $federationCountries;
    }

    /**
     * @param $federationCountries
     * @param $countryName
     * @return int|string|null
     */
    static public function getCodeByCountryName($federationCountries, $countryName)
    {
        $pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'á'", "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'");
        $replace = array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'a', 'A', 'A', 'c', 'C');
        $countryNameStripped = preg_replace($pattern, $replace, $countryName);
        foreach ($federationCountries as $key => $country) {
            if (strtolower($country) === strtolower($countryName)) {
                return $key;
            }
            $countryStripped = preg_replace($pattern, $replace, $country);
            if (strtolower($countryStripped) === strtolower($countryNameStripped)) {
                return $key;
            }
        }
        return null;
    }

}
