<?php

namespace App\Service\Crawler\Entity\Country;

use App\Entity\Country;
use App\Entity\Federation;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CountryDropDownTool;
use App\Tool\FederationTool;
use App\Tool\FifaSite\FifaSiteFederationTool;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CountryCrawler
 * @package App\Service\Entity\Country
 */
class CountryCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var Country[]
     */
    private $countries = [];

    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $collectionConfig = $this->getConfigManager()
            ->getValue('country.collection.url')
        ;

        $countryCollection = MetadataSchemaResources::createSchema($collectionConfig);

        $countryItemConfig = $this
            ->getConfigManager()
            ->getValue('country.item.url');

        $countryItemMetadata = MetadataSchemaResources::createSchema($countryItemConfig);

        try {
            $this->createProgressBar('Crawling countries from resource', 2);

            $this
                ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COUNTRY))
                ->processPath($countryCollection->getUrl(), $countryCollection->getMethod());
            $this->advanceProgressBar();

            $countryItem = CountryDropDownTool::getCodesAndNamesFromDropDown($this->getCrawler());

            $this->advanceProgressBar();
            $this->finishProgressBar();

            if (!empty($countryItem)) {
                $this->createProgressBar('Processing countries', count($countryItem['keys']));
                foreach ($countryItem['keys'] as $key => $id) {
                    $url = $this->preparePath($countryItemMetadata->getUrl(), [$id]);
//                    $this->processPath($url);
                    $name = $countryItem['names'][$key];
                    $schema = new MetadataSchemaResources();
                    $schema->setUrl($url);
                    $this->countries[] = $this->createCountry($id, $name, $schema->getSchema());
                    $this->advanceProgressBar();
                }
                $this->finishProgressBar();

            }
        }
        catch(\Exception $e) {
            if ($this->getOutput() instanceof OutputInterface) {
                $this->getOutput()->writeln('An error has occurred');
                $this->getOutput()->writeln($e->getMessage());
            }
        }

        $this->processFederationAndCode();
        $this->processFlagImage();

        return $this;
    }


    /**
     * @return Country[]
     */
    public function getData()
    {
        return $this->countries;
    }

    /**
     * @return $this|CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        $this->createProgressBar('Saving countries', count($this->countries));
        /* @var $country Country */
        foreach ($this->countries as $country) {
            $em = $this->getDoctrine()
                ->getManager();
            $em->persist($country);
            $em->flush();
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();

        return $this;
    }

    /**
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function processFederationAndCode()
    {
        $countryFifaSchema = $this->getConfigSchema('country.fifa.federation.url');
        $federations = $this->getDoctrine()
            ->getRepository(Federation::class)
            ->findBy(['shortname' => [
                FederationTool::UEFA_FEDERATION,
                FederationTool::CONMEBOL_FEDERATION,
                FederationTool::CONCACAF_FEDERATION,
                FederationTool::AFC_FEDERATION,
                FederationTool::CAF_FEDERATION,
                FederationTool::OFC_FEDERATION,
            ]]);
        $federationCountries = [];
        foreach ($federations as $federation) {
            $federationName = strtolower($federation->getShortname());
            $url = $this->preparePath($countryFifaSchema->getUrl(),[$federationName]);
            $this
                ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COUNTRY))
                ->processPath($url);
            $federationCountries[$federation->getId()] = FifaSiteFederationTool::getFederationCountries($this->getCrawler());
        }

        foreach ($this->countries as $country) {
            foreach ($federations as $federation) {
                $code = FifaSiteFederationTool::getCodeByCountryName(
                    $federationCountries[$federation->getId()],
                    $country->getName()
                );
                if ($code !== null) {
                    $country->setFederation($federation);
                    $country->setCode($code);
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function processFlagImage()
    {
        $countryFile = $this->getRootFolder() . '/public/files/country-flags/countries.json';
        $content = file_get_contents($countryFile);
        $countryFlags = json_decode($content, true);
        foreach ($this->countries as $country) {
            foreach ($countryFlags as $key => $name) {
                if ($country->getName() === $name) {
                    $country->setImage('/country-flags/svg/' . strtolower($key) . '.svg');
                }
            }
        }
        return $this;
    }

    /**
     * @param $tmkCode
     * @param $name
     * @param array $metadata
     * @return Country
     */
    private function createCountry($tmkCode, $name, array $metadata = [])
    {
        $country = $this->getDoctrine()
            ->getRepository(Country::class)
            ->findOneByTmkCode($tmkCode);
        if (!$country instanceof Country) {
            $country = new Country();
            $country->setTmkCode($tmkCode);
        }
        $country->setName($name);
        $country->setMetadata($metadata);
        return $country;
    }




}
