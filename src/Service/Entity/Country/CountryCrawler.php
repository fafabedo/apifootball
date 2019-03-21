<?php

namespace App\Service\Entity\Country;

use App\Entity\Country;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Service\Config\ConfigManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CountryCrawler
 * @package App\Service\Entity\Country
 */
class CountryCrawler extends ContentCrawler implements CrawlerInterface
{
    private $countries = [];

    /**
     * CountryCrawler constructor.
     * @param ManagerRegistry $doctrine
     * @param ConfigManager $configManager
     */
    public function __construct(
        ManagerRegistry $doctrine,
        ConfigManager $configManager)
    {
        parent::__construct($doctrine, $configManager);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $collection = $this->getConfigManager()
            ->getValue('country.collection.url')
        ;

        try {
            $this->createProgressBar('Crawling countries from resource', 10);

            $this->processPath($collection['resource']['url'], $collection['resource']['method']);

            $this->advanceProgressBar(5);
            $content = $this
                ->getCrawler()
                ->html()
            ;
            $this->advanceProgressBar(5);
            $this->finishProgressBar();

            preg_match_all('/([0-9]+)...\>([a-zA-Z|\s]+)/i', $content, $matches);

            $countryKeys = $matches[1] ?? [];
            $countryNames = $matches[2] ?? [];
            if (is_array($countryKeys) && is_array($countryNames)) {

                $this->createProgressBar('Processing countries', count($countryKeys));
                foreach ($countryKeys as $key => $id) {
                    $name = $countryNames[$key];
                    $this->countries[] = $this->createCountry($id, $name);
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
        /* @var $country Country */
        foreach ($this->countries as $country)
        {
            $code = $country->getCode();
            $existent = $this->getDoctrine()
                ->getRepository(Country::class)
                ->findOneBy(['code' => $code]);

            if ($existent instanceof Country) {
                $existent->setName($country->getName());
                $existent->setCode($country->getCode());
                $country = $existent;
            }

            $em = $this->getDoctrine()
                ->getManager();

            $em->persist($country);
            $em->flush();
        }

        return $this;
    }

    /**
     * @param $id
     * @param $name
     * @return Country
     */
    private function createCountry($id, $name)
    {
        $country = new Country();
        $country->setName($name);
        $country->setCode($id);

        return $country;

    }




}