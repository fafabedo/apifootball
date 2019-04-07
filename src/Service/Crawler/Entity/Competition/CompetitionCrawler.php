<?php


namespace App\Service\Crawler\Entity\Competition;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionType;
use App\Entity\Country;
use App\Entity\TeamType;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\CompetitionMainPageTool;
use App\Tool\FilesystemTool;
use App\Tool\HtmlTool;
use App\Tool\UrlTool;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CompetitionCrawler
 * @package App\Service\Entity\Competition
 */
class CompetitionCrawler extends ContentCrawler implements CrawlerInterface
{
    private const COMPETITION_FOLDER = 'competition';

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
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $this->processCodes();

        if (empty($this->codes)) {
            return $this;
        }
        $competitionItemSchema = $this
            ->getConfigSchema('competition.item.url');
        $this->createProgressBar('Crawling country information', count($this->codes));

        foreach ($this->codes as $code => $country) {
            $url = $this->preparePath($competitionItemSchema->getUrl(), [$code]);
            $this->processPath($url, $competitionItemSchema->getMethod());

            $url = HtmlTool::getCanonical($this->getCrawler());
            $slug = UrlTool::getParamFromUrl($url, 1);
            $name = CompetitionMainPageTool::getNameCompetition($this->getCrawler());
            $type = CompetitionMainPageTool::processTypeCompetition($this->getCrawler());
            $level = CompetitionMainPageTool::getLeagueLevel($this->getCrawler());
            $teams = CompetitionMainPageTool::getNumberTeams($this->getCrawler());
            switch ($type) {
                case CompetitionMainPageTool::TOURNAMENT:
                    $imageUrl = CompetitionMainPageTool::getImageFromCompetition($this->getCrawler());
                    break;
                default:
                case CompetitionMainPageTool::LEAGUE:
                    $imageUrl = CompetitionMainPageTool::getImageFromCompetition($this->getCrawler());
                    break;
            }
            $destination = FilesystemTool::getDestination(
                $this->getRootFolder(),
                self::COMPETITION_FOLDER,
                $code,
                FilesystemTool::getExtension($imageUrl)
                );
            $filename = null;
            if (FilesystemTool::persistFile($imageUrl, $destination) === true) {
                $filename = FilesystemTool::getFilename(self::COMPETITION_FOLDER,
                    $code,
                    FilesystemTool::getExtension($imageUrl));
            }

            $teamType = $this->getDoctrine()
                ->getRepository(TeamType::class)
                ->find(1);

            if ($type === CompetitionMainPageTool::TOURNAMENT
                || ($level < 6 && $teams > 0)) {
                $competition = $this->findCompetitionByCode($code);
                if (!$competition instanceof Competition) {
                    $competition = new Competition();
                    $competition->setCode($code);
                }
                $competition->setName($name);
                $competition->setLeagueLevel($level);
                $competition->setNumberTeams($teams);
                $competition->setCompetitionType($this->getCompetitionType($type));
                $competition->setTeamType($teamType);
                $competition->setCountry($country);
                $competition->setImage($filename);
                $competition->setSlug($slug);
                $schema = MetadataSchemaResources::createSchema()
                    ->setUrl($url);
                $competition->setMetadata($schema->getSchema());
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
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     */
    private function addCompetitionSeasons(Competition $competition, $compSeasons = []): Competition
    {
        $globalSchema = $this->getConfigSchema('global.url');;

        $archive = false;
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
            $compSeason->setArchive($archive);
            if (isset($season['url'])) {
                $schema = MetadataSchemaResources::createSchema()
                    ->setUrl($globalSchema->getUrl() . $season['url']);
                $compSeason->setMetadata($schema->getSchema());
            }
            $competition->addCompetitionSeason($compSeason);
            $archive = true;
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
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processCodes()
    {
        $collectionSchema = $this
            ->getConfigSchema('competition.collection.url');

        $countries = $this->getCountryList();
        $this->createProgressBar('Crawling country codes', count($countries));

        foreach ($countries as $country) {
            $params = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'land_id' => $country->getTmkCode(),
                ],
            ];
            $codes = $this
                ->processPath($collectionSchema->getUrl(), $collectionSchema->getMethod(), $params)
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
        $this->createProgressBar('Saving competitions', count($this->competitions));
        $em = $this->getDoctrine()
            ->getManager();
        foreach ($this->competitions as $competition) {
            $competition = $this->getMergedCompetition($competition);
            $em->persist($competition);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @param Competition $competition
     * @return Competition
     */
    private function getMergedCompetition(Competition $competition): Competition
    {
        $code = $competition->getCode();
        $existent = $this->getDoctrine()
            ->getRepository(Competition::class)
            ->findOneBy(['code' => $code]);

        if ($existent instanceof Competition) {
            $existent->setLeagueLevel($competition->getLeagueLevel());
            $existent->setNumberTeams($competition->getNumberTeams());
            $existent->setTeamType($competition->getTeamType());
            $existent->setCountry($competition->getCountry());
            $competition = $existent;
        }
        return $competition;
    }

    private function findCompetitionByCode($code)
    {
        $competition = $this->getDoctrine()
            ->getRepository(Competition::class)
            ->findOneBy(['code'=>$code]);
        if (!$competition instanceof Competition) {
            return null;
        }
        return $competition;
    }

    private function getCompetitionType($type)
    {
        return $this
            ->getDoctrine()
            ->getRepository(CompetitionType::class)
            ->findOneBy(['name' => $type]);
    }

}
