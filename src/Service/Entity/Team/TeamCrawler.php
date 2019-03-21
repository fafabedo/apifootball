<?php

namespace App\Service\Entity\Team;

use App\Entity\Competition;
use App\Entity\Country;
use App\Entity\Team;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class TeamCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var Team[]
     */
    private $teams = [];

    /**
     * @var array
     */
    private $teamsByComp = [];
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
     * @return array
     */
    private function getListTeamFromContent()
    {
        return $this
            ->getCrawler()
            ->filterXPath('//*[@id="yw1"]//tbody//tr')
            ->each(function (Crawler $node, $i) {
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
                $team = new Team();
                $team->setCountry($this->getCountry());
                $team->setName($name[0]);
                $team->setShortname($name[1]);
                $team->setMetadata(
                    [
                        'resource' => [
                            'url' => $url,
                            'method' => 'GET'
                        ]
                    ]);

                return $team;
            });
    }

    public function processAAA()
    {
        $teams = [];
        foreach ($teams as $team) {
            $teamObj = new Team();
            preg_match('/([0-9]+)\//i', $team['url'], $matches);
            if (isset($matches[1])) {
                $teamObj->setCode($matches[1]);
            }
            $teamObj->setName($team['full_name']);
            $teamObj->setShortname($team['name']);
            if ($this->getCompetition() instanceof Competition) {
                $teamObj->setCompetition($this->getCompetition());
                $teamObj->setCountry($this->getCompetition()->getCountry());
            }
            $this->teams[] = $teamObj;
        }

        return $this;
    }

    /**
     * @return Competition[]|object[]
     */
    public function getCompetitions(): array
    {
        if ($this->getCountry() instanceof Country) {
            return $this->getDoctrine()
                ->getRepository(Competition::class)
                ->findBy(['country' => $this->getCountry()]);
        }
        return $this->getDoctrine()
            ->getRepository(Competition::class)
            ->findAll();
    }

    public function process(): CrawlerInterface
    {
        $collection = $this
            ->getConfigManager()
            ->getValue('team.collection.url');

        $competitions = $this->getCompetitions();

        $this->createProgressBar('Crawling competitions to scope', count($competitions));

        foreach ($competitions as $competition) {
            if ($competition->getLeagueLevel() > $this->getLevel()) {
                $this->advanceProgressBar();
                continue;
            }
            $code = $competition->getCode();
            $url = $this->preparePath($collection['resource']['url'], [$code]);
            $this->processPath($url, $collection['resource']['method']);
            $this
                ->setCompetition($competition)
                ->setCountry($competition->getCountry())
            ;
            $teams = $this->getListTeamFromContent();
            $this->teams = array_merge($this->teams, $teams);

            $this->advanceProgressBar();
        }

        $this->finishProgressBar();

        if (!empty($this->teams)) {
            $this->createProgressBar('Processing clubs', count($this->teams));
            foreach ($this->teams as $team) {
                if (isset($collection['resource']['base_url'])) {
                    $metadata = $team->getMetadata();
                    $metadata['resource']['url'] = $collection['resource']['base_url'] . $metadata['resource']['url'];
                    $team->setMetadata($metadata);
                }
                $this->advanceProgressBar();
            }
            $this->finishProgressBar();
        }
        return $this;
    }

    /**
     * @return Team[]
     */
    public function getData()
    {
        return $this->teams;
    }

    /**
     *
     */
    public function saveData(): CrawlerInterface
    {
        foreach ($this->teams as $team) {
            $code = $team->getCode();
            $existent = $this->getDoctrine()
                ->getRepository(Team::class)
                ->findOneBy(['code' => $code]);

            if ($existent instanceof Team) {
                $existent->setShortname($team->getShortname());
                $existent->setName($team->getName());
                $team = $existent;
            }

            $em = $this->getDoctrine()
                ->getManager();

            $em->persist($team);
            $em->flush();
        }
        return $this;
    }

}