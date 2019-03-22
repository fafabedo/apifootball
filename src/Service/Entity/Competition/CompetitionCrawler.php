<?php


namespace App\Service\Entity\Competition;


use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionType;
use App\Entity\Country;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CompetitionCrawler
 * @package App\Service\Entity\Competition
 */
class CompetitionCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var Competition[]
     */
    private $competitions = [];

    /**
     * @var array
     */
    private $codes = [];

    /**
     * @var Country
     */
    private $country;

    /**
     * @var string
     */
    private $code;

    /**
     * @return Country
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     * @return $this
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return CompetitionCrawler
     */
    public function setCode(string $code): CompetitionCrawler
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return Country[]|object[]
     */
    public function getCountryList()
    {
        if ($this->getCountry() instanceof Country) {
            return [$this->getDoctrine()
                ->getRepository(Country::class)
                ->find($this->getCountry()->getId())];
        }
        return $this
            ->getDoctrine()
            ->getRepository(Country::class)
            ->findBy(['active' => true]);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $this->processCodes();

        if (empty($this->codes)) {
            return $this;
        }

        $this->createProgressBar('Crawling country information', count($this->codes));

        foreach ($this->codes as $code => $country) {

            $item = $this
                ->getConfigManager()
                ->getValue('competition.item.url');

            $url = $this->preparePath($item['resource']['url'], [$code]);
            $this->processPath($url, $item['resource']['method']);

            if ($this
                    ->getCrawler()
                    ->filter('h1.spielername-profil')->count() === 0) {
                continue;
            }
            $nameHtml = $this
                ->getCrawler()
                ->filter('h1.spielername-profil')
                ;
            if (!$nameHtml instanceof Crawler) {
                continue;
            }
            $name = $nameHtml->html();

            $tableHtml = $this
                ->getCrawler()
                ->filter('table.profilheader')
            ;
            if (!$tableHtml instanceof Crawler) {
                continue;
            }
            $table = $tableHtml->html();

            $stripped = strip_tags(preg_replace('/\s+/', ' ', $table));
            preg_match('/League\sLevel:[\s]+([a-zA-Z|\s]*)/', $stripped, $matches);

            switch ($matches[1]) {
                case 'First Tier':
                    $level = 1;
                    break;
                case 'Second Tier':
                    $level = 2;
                    break;
                case 'Third Tier':
                    $level = 3;
                    break;
                case 'Fourth Tier':
                    $level = 4;
                    break;
                case 'Fifth Tier':
                    $level = 5;
                    break;
                default:
                    $level = 6;
                    break;
            }

            preg_match('/Number\sof\steams:[\s]+([0-9]*)/', $stripped, $matches);

            $competitionType = $this->getDoctrine()
                ->getRepository(CompetitionType::class)
                ->find(1);

            if ($level < 5 && isset($matches[1])) {
                $teams = (int)$matches[1];

                $competition = new Competition();
                $competition->setName($name);
                $competition->setLeagueLevel($level);
                $competition->setNumberTeams($teams);
                $competition->setCode($code);
                $competition->setCompetitionType($competitionType);
                $competition->setCountry($country);
                $competition = $this->getMergedCompetition($competition);
                $compSeasons = $this->getCompetitionSeasonUrl();
                $competition = $this->addCompetitionSeasons($competition, $compSeasons);
                $this->competitions[] = $competition;
            }
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();

        return $this;
    }

    /**
     * @param Competition $competition
     * @param array $compSeasons
     * @return Competition
     */
    private function addCompetitionSeasons(Competition $competition, $compSeasons = []): Competition
    {
        $globalConfig = $this->getConfigManager()
            ->getValue('global.url')
        ;

        $active = true;
        foreach ($compSeasons as $year => $season) {
            /* @var \DateTime $starts
             * @var \DateTime $ends
             */
            $starts = $season['starts'];
            $ends = $season['ends'];
            $result = $competition->getCompetitionSeasons()->filter(function(CompetitionSeason $competitionSeason) use ($starts) {
                return (
                    $competitionSeason->getStartSeason()!== null
                    && $competitionSeason->getStartSeason()->getTimestamp() == $starts->getTimestamp()
                );
            })->first();
            $compSeason = new CompetitionSeason();
            if ($result instanceof CompetitionSeason) {
                $compSeason = $result;
            }
            $compSeason->setCompetition($competition);
            $compSeason->setStartSeason($starts);
            $compSeason->setEndSeason($ends);
            $compSeason->setArchive($active);
            $metadata = [
                'resource' => [
                    'url' => $globalConfig['url'] . $season['url'],
                    'method' => 'GET',
                ]
            ];
            $compSeason->setMetadata($metadata);
            $competition->addCompetitionSeason($compSeason);
            $active = false;
        }

        return $competition;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getCompetitionSeasonUrl(): array
    {
        $seasonUrl = [];
        $seasons = $this
            ->getCrawler()
            ->filterXPath('//div[@id="wettbewerbsstartseite"]//div[@class="content"]');
        if ($seasons->count() > 0) {
            $seasonUrl = [];
            $html = $seasons->html();
            preg_match('/action="([a-zA-Z0-9-|\/]*)"/', $html, $matches);
            if (isset($matches[1])) {
                $relativePath = $matches[1];
            }

            preg_match('/\<select\sname="([a-z|_|-]+)"/', $seasons->html(), $matchesParamName);
            if (isset($matchesParamName[1])) {
                $paramName = $matchesParamName[1];
            }

            preg_match_all('/value="([0-9]+)">([0-9|\/]+)/', $seasons->html(), $matchesParams);
            if (isset($matchesParams[1])
                && isset($matchesParams[2])
                && isset($relativePath)
                && isset($paramName)
            ) {
                foreach ($matchesParams[1] as $key => $value) {
                    if ($value > 1990) {
                        $period = $matchesParams[2][$key];
                        preg_match('/([0-9]+)\/([0-9]+)/', $period, $periods);
                        switch (true) {
                            case (isset($periods[1]) && isset($periods[2])):
                                $starts = strtotime($periods[1] . '-08-01');
                                $ends = strtotime($periods[2] . '-06-01');
                                break;
                            default:
                                $starts = strtotime($period . '-01-1');
                                $ends = strtotime($period. '-12-1');
                        }
                        $startAt = new \DateTime();
                        $startAt->setTimestamp($starts);
                        $endAt = new \DateTime();
                        $endAt->setTimestamp($ends);
                        $seasonUrl[$value] = [
                            'url' =>$relativePath . '?' . $paramName . '=' . $value,
                            'starts'=> $startAt,
                            'ends' => $endAt,
                        ];
                    }
                }
            }
        }
        return $seasonUrl;
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processCodes()
    {
        $collection = $this
            ->getConfigManager()
            ->getValue('competition.collection.url');

        $countries = $this->getCountryList();
        $this->createProgressBar('Crawling country codes', count($countries));

        foreach ($countries as $country) {
            $params = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'land_id' => $country->getCode(),
                ],
            ];
            $codes = $this
                ->processPath($collection['resource']['url'], $collection['resource']['method'], $params)
                ->setCountry($country)
                ->getCompetitionCodes();
            $this->codes += $codes;
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();

        return $this;
    }

    /**
     * @return array
     */
    public function getCompetitionCodes()
    {
        $content = $this->getContent();
        preg_match_all('/"([a-zA-Z0-9]+).\>([a-zA-Z|\s|\p{L}]+)/i', $content, $matches);
        $competitions = [];
        foreach ($matches[1] as $key => $item) {
            $competitions[$item] = $this->getCountry();
        }
        return $competitions;
    }


    /**
     * @return Competition[]
     */
    public function getData()
    {
        return $this->competitions;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        foreach ($this->competitions as $competition) {
            $competition = $this->getMergedCompetition($competition);

            $em = $this->getDoctrine()
                ->getManager();

            $em->persist($competition);
            $em->flush();
        }
        return $this;
    }

    private function getMergedCompetition(Competition $competition): Competition
    {
        $code = $competition->getCode();
        $existent = $this->getDoctrine()
            ->getRepository(Competition::class)
            ->findOneBy(['code' => $code]);

        if ($existent instanceof Competition) {
            $existent->setLeagueLevel($competition->getLeagueLevel());
            $existent->setNumberTeams($competition->getNumberTeams());
            $existent->setCountry($competition->getCountry());
            $competition = $existent;
        }
        return $competition;
    }

}