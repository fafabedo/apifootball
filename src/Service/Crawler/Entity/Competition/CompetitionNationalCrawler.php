<?php


namespace App\Service\Crawler\Entity\Competition;

use App\Entity\Competition;
use App\Entity\Country;
use App\Entity\Federation;
use App\Entity\Team;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CountryPageTool;
use App\Tool\TypeTool;
use App\Tool\UrlTool;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class CompetitionNationalCrawler
 * @package App\Service\Entity\Competition
 */
class CompetitionNationalCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var array
     */
    private $teams = [];

    /**
     * @var bool
     */
    private $onlyActive = true;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var Competition
     */
    private $competition;

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     * @return CompetitionNationalCrawler
     */
    public function setCountry($country): CrawlerInterface
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return Competition
     */
    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     * @return CompetitionNationalCrawler
     */
    public function setCompetition(Competition $competition): CompetitionNationalCrawler
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOnlyActive(): bool
    {
        return $this->onlyActive;
    }

    /**
     * @param bool $onlyActive
     * @return CompetitionNationalCrawler
     */
    public function setOnlyActive(bool $onlyActive): CompetitionNationalCrawler
    {
        $this->onlyActive = $onlyActive;
        return $this;
    }


    /**
     * @return CrawlerInterface
     * @throws GuzzleException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     */
    public function process(): CrawlerInterface
    {
        $countries = $this->getCountryList();
        $this->createProgressBar('Crawl national teams', count($countries));
        foreach ($countries as $country) {
            $metadata = $country->getMetadata();
            $schema = MetadataSchemaResources::createSchema($metadata);
            if ($schema->getUrl() !== null) {
                $url = $this->preparePath($schema->getUrl(), [$country->getCode()]);
                $this
                    ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION))
                    ->processPath($url, $schema->getMethod())
                ;
                $teamList = CountryPageTool::getNationalTeamLinks($this->getCrawler());
                $teams = $this->createTeams($country, $teamList);
                $this->teams = array_merge($this->teams, $teams);
            }
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->teams;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        $this->createProgressBar('Saving National teams', count($this->teams));
        $em = $this
            ->getDoctrine()
            ->getManager();
        foreach ($this->teams as $team) {
            $em->persist($team);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();

        return $this;
    }

    /**
     * @return Country[]|object[]
     */
    private function getCountryList()
    {
        $filters = [];
        if ($this->getCountry() instanceof Country) {
            $filters['id'] = $this->getCountry()->getId();
        }
        if ($this->isOnlyActive() === true) {
            $filters['active'] = true;
        }
        return $this
            ->getDoctrine()
            ->getRepository(Country::class)
            ->findBy($filters);
    }

    /**
     * @param Country $country
     * @param array $list
     * @return array
     * @throws GuzzleException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     */
    private function createTeams(Country $country, array $list = [])
    {
        $teams = [];
        foreach ($list as $key => $teamData) {
            $teamUrl = $this->getBaseUrl()->getUrl() . $teamData['url'];
            $tmkCode = UrlTool::getParamFromUrl($teamUrl, 4);
            $slug = UrlTool::getParamFromUrl($teamUrl, 1);
            $this
                ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION))
                ->processPath($teamUrl)
            ;
            $federationName = CountryPageTool::getFederationFromCountryPage($this->getCrawler());
            $federation = $this->manageFederation($federationName);
            $this->updateCountryFederation($country, $federation);
            $team = $this->getDoctrine()
                ->getRepository(Team::class)
                ->findOneByTmkCode($tmkCode);
            if ($team === null) {
                $team = new Team();
                $team->setTmkCode($tmkCode);
            }
            $team->setCountry($country);
            $team->setName($teamData['name']);
            $team->setShortname($teamData['name']);
            $team->setSlug($slug);
            $team->setIsYouthTeam(($key==0 ? false : true));
            $team->setTeamType(TypeTool::getNationalTypeTeam($this->getDoctrine()));
            $schema = MetadataSchemaResources::createSchema()
                ->setUrl($teamUrl);
            $team->setMetadata($schema->getSchema());

            $teams[] = $team;
        }
        return $teams;
    }

    /**
     * @param Country $country
     * @param Federation $federation
     * @return $this
     */
    private function updateCountryFederation(Country $country, Federation $federation)
    {
        if (!$country->getFederation() instanceof Federation) {
            $country->setFederation($federation);
            $em = $this->getDoctrine()->getManager();
            $em->persist($country);
            $em->flush();
        }
        return $this;
    }

    /**
     * @return MetadataSchemaResources
     * @throws \App\Exception\InvalidMetadataSchema
     */
    private function getBaseUrl(): MetadataSchemaResources
    {
        return $this->getConfigSchema('global.url');
    }

    /**
     * @param $name
     * @return Federation
     */
    private function manageFederation($name): Federation
    {
        $federation = $this
            ->getDoctrine()
            ->getRepository(Federation::class)
            ->findOneBy(['shortname' => $name]);
        if (!$federation instanceof Federation) {
            $federation = new Federation();
            $federation->setShortname($name);
            $federation->setName($name);
            $em = $this->getDoctrine()->getManager();
            $em->persist($federation);
            $em->flush();
            $federation = $em->merge($federation);
        }
        return $federation;
    }

}
