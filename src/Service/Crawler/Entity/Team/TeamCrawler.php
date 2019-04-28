<?php

namespace App\Service\Crawler\Entity\Team;

use App\Entity\Competition;
use App\Entity\Country;
use App\Entity\Team;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionMainPageTool;
use App\Tool\TypeTool;
use App\Tool\UrlTool;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class TeamCrawler
 * @package App\Service\Crawler\Entity\Team
 */
class TeamCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var Team[]
     */
    private $teams = [];

    /**
     * @var Competition
     */
    private $competition;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var integer
     */
    private $level = 1;

    /**
     * @return mixed
     */
    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     * @return TeamCrawler
     */
    public function setCompetition($competition)
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     * @return TeamCrawler
     */
    public function setCountry(Country $country): TeamCrawler
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return TeamCrawler
     */
    public function setLevel(int $level): TeamCrawler
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $teamCollection = $this->getConfigSchema('team.collection.url');
        $competitions = $this->getCompetitions();

        $this->createProgressBar('Crawling competitions to scope', count($competitions));

        foreach ($competitions as $competition) {
            $code = $competition->getCode();
            $url = $this->preparePath($teamCollection->getUrl(), [$code]);
            $this
                ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_TEAM))
                ->processPath($url);
            if ($competition->getCountry() instanceof Country) {
                $this
                    ->setCompetition($competition)
                    ->setCountry($competition->getCountry());
            }
            $teamCodes = CompetitionMainPageTool::getTeamsFromPage($this->getCrawler());
            $teams = $this->processTeamCodes($competition, $teamCodes);
            $this->teams = array_merge($this->teams, $teams);

            $this->advanceProgressBar();
        }

        $this->finishProgressBar();

        if (!empty($this->teams)) {
            $this->createProgressBar('Processing clubs', count($this->teams));
            foreach ($this->teams as $team) {
                $metadata = $team->getMetadata();
                $team->setMetadata($metadata);
                $this->advanceProgressBar();
            }
            $this->finishProgressBar();
        }
        return $this;
    }

    /**
     * Retrieve created teams
     * @return Team[]
     */
    public function getData()
    {
        return $this->teams;
    }

    /**
     * Save created teams in database
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        $this->createProgressBar('Save teams processed', count($this->teams));
        $em = $this->getDoctrine()
            ->getManager();
        foreach ($this->teams as $team) {
            $em->persist($team);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->advanceProgressBar();
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @param Competition $competition
     * @param $teamCodes
     * @return array
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     */
    private function processTeamCodes(Competition $competition, $teamCodes): array
    {
        $globalSchema = $this->getConfigSchema('global.url');
        $teams = [];
        foreach ($teamCodes as $item) {
            $url = $globalSchema->getUrl() . $item['url'];
            $tmkCode = UrlTool::getParamFromUrl($url,4);
            $team = $this
                ->getDoctrine()
                ->getRepository(Team::class)
                ->findOneBy(['tmk_code'=> $tmkCode]);
            if (!$team instanceof Team) {
                $team = new Team();
                $team->setTmkCode($tmkCode);
            }
            $team->setCountry($this->getCompetition()->getCountry());
            $team->setName($item['name']);
            $team->setShortname($item['shortname']);
            $team->setTeamType(TypeTool::getClubTypeTeam($this->getDoctrine()));
            $team->setIsYouthTeam($competition->getIsYouthCompetition() ? true : false);
            $metadataSchema = new MetadataSchemaResources();
            $metadataSchema->setUrl($url);
            $team->setMetadata($metadataSchema->getSchema());
            $teams[] = $team;
        }
        return $teams;
    }

    /**
     * @return array
     */
    private function getListTeamFromContent()
    {
        $globalConfig =$this
            ->getConfigManager()
            ->getValue('global.url')
        ;
        return $this
            ->getCrawler()
            ->filterXPath('//*[@id="yw1"]//tbody//tr')
            ->each(function (Crawler $node, $i) use($globalConfig) {
                $href = $node
                    ->each(function (Crawler $nodeChild, $i) {
                        $filtered = $nodeChild->filter('td')->html();
                        preg_match('/href="([a-zA-Z0-9|_|\/|-]+)"/i', $filtered, $matches);
                        if (!isset($matches[1])) {
                            return '';
                        }
                        return $matches[1];
                    });

                $url = $href[0];
                $names = $node->filterXPath('//a')
                    ->each(function (Crawler $nodeChild, $i) {
                        return $nodeChild->text();
                    });
                $name = [];
                foreach ($names as $text) {
                    if (!empty($text)) {
                        $name[] = $text;
                    }
                    if (count($name) == 2) {
                        break;
                    }
                }
                preg_match('/([^\/]+)\/[^\/]+\/[^\/]+$/', $url, $codeMatches);

                $team = $tmkCode = null;
                if (isset($codeMatches[1])) {
                    $tmkCode = $codeMatches[1];
                    $team = $this
                        ->getDoctrine()
                        ->getRepository(Team::class)
                        ->getTeamByCode($tmkCode);
                }
                if (!$team instanceof Team) {
                    $team = new Team();
                    $team->setTmkCode($tmkCode);
                }
                $team->setCountry($this->getCompetition()->getCountry());
                $team->setName($name[0]);
                $team->setShortname($name[1]);
                $team->setTeamType(TypeTool::getClubTypeTeam($this->getDoctrine()));
                $team->setIsYouthTeam(false);
                $metadataSchema = new MetadataSchemaResources();
                $metadataSchema->setUrl($globalConfig['url'] . $url);
                $team->setMetadata($metadataSchema->getSchema());

                return $team;
            });
    }

    /**
     * @return Competition[]|object[]
     */
    public function getCompetitions(): array
    {
        $filters = [];
        if ($this->getCountry() instanceof Country) {
            $filters['country'] = $this->getCountry();
        }
        $levels = range(1, $this->getLevel());
        $filters['league_level'] = $levels;
        if ($this->getCompetition() instanceof Competition) {
            $filters['competition'] = $this->getCompetition();
        }
        return $this->getDoctrine()
            ->getRepository(Competition::class)
            ->findBy($filters);
    }

}
