<?php


namespace App\Service\Crawler\Entity\Competition;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionType;
use App\Entity\Country;
use App\Entity\TeamType;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionMainPageTool;
use App\Tool\FilesystemTool;
use App\Tool\HtmlTool;
use App\Tool\UrlTool;
use Defuse\Crypto\File;
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
     * @var bool
     */
    private $updateImageActive = false;

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
     * @return bool
     */
    public function isUpdateImageActive(): bool
    {
        return $this->updateImageActive;
    }

    /**
     * @param bool $updateImageActive
     * @return CompetitionCrawler
     */
    public function setUpdateImageActive(bool $updateImageActive): CompetitionCrawler
    {
        $this->updateImageActive = $updateImageActive;

        return $this;
    }

    /**
     * @return Country[]|object[]
     */
    public function getCountryList()
    {
        if ($this->getCountry() instanceof Country) {
            return [
                $this->getDoctrine()
                    ->getRepository(Country::class)
                    ->find($this->getCountry()->getId()),
            ];
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
        $codes = $this->pullCompetitionCodes();
        if (empty($codes)) {
            return $this;
        }
        $competitionItemSchema = $this
            ->getConfigSchema('crawler.competition.item.url');
        $this->createProgressBar('Crawling country information', count($codes));

        $index = 1;
        foreach ($codes as $countryId => $leagueCodes) {
            if (!$this->validOffset($index)) {
                $index++;
                continue;
            }
            foreach ($leagueCodes as $tmkCode) {
                $url = $this->preparePath($competitionItemSchema->getUrl(), [$tmkCode]);
                $this
                    ->setLifetime($this->getLifeTimeValue())
                    ->processPath($url, $competitionItemSchema->getMethod());
                try {
                    $url = HtmlTool::getCanonical($this->getCrawler());
                    $slug = UrlTool::getParamFromUrl($url, 1);
                    $name = CompetitionMainPageTool::getNameCompetition($this->getCrawler());
                    $type = CompetitionMainPageTool::processTypeCompetition($this->getCrawler());
                    $isYouth = CompetitionMainPageTool::isYouthCompetition($this->getCrawler());
                    $level = CompetitionMainPageTool::getLeagueLevel($this->getCrawler());
                    $teams = CompetitionMainPageTool::getNumberTeams($this->getCrawler());
                    $imageUrl = CompetitionMainPageTool::getImageFromCompetition($this->getCrawler());
                    $filename = $this
                        ->processImageUrl($imageUrl, $tmkCode, self::COMPETITION_FOLDER);

                    $teamType = $this->getDoctrine()
                        ->getRepository(TeamType::class)
                        ->find(1);

                    if ($type === CompetitionMainPageTool::TOURNAMENT
                        || ($level < 6 && $teams > 0)) {
                        $competition = $this->getDoctrine()
                            ->getRepository(Competition::class)
                            ->findOneByTmkCode($tmkCode);
                        if (!$competition instanceof Competition) {
                            $competition = new Competition();
                            $competition->setTmkCode($tmkCode);
                        }
                        $competition->setName($name);
                        $competition->setLeagueLevel($level);
                        $competition->setNumberTeams($teams);
                        $competition->setCompetitionType($this->getCompetitionType($type));
                        $competition->setTeamType($teamType);
                        $competition->setIsYouthCompetition($isYouth);
                        $country = $this->getDoctrine()
                            ->getRepository(Country::class)
                            ->find($countryId);
                        $competition->setCountry($country);
                        if (isset($filename)) {
                            $competition->setImage($filename);
                        }
                        $competition->setSlug($slug);
                        $schema = MetadataSchemaResources::createSchema()
                            ->setUrl($url);
                        $competition->setMetadata($schema->getSchema());
                        $this->competitions[] = $competition;
                    }
                } catch (\Exception $e) {

                }
            }
            $index++;
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @return array
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pullCompetitionCodes()
    {
        $collectionSchema = $this
            ->getConfigSchema('crawler.competition.collection.url');

        $countries = $this->getCountryList();
        $this->createProgressBar('Crawling country codes', count($countries));

        $codes = [];
        foreach ($countries as $country) {
            $params = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'land_id' => $country->getTmkCode(),
                ],
            ];

            $this
                ->setLifetime($this->getLifeTimeValue())
                ->processPath($collectionSchema->getUrl(), $collectionSchema->getMethod(), $params);

            $compCodes = $this->getCompetitionCodes();
            $codes[$country->getId()] = $compCodes;
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();

        return $codes;
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
            $competitions[] = $item;
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
            $em->persist($competition);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();

        return $this;
    }

    /**
     * @return int
     */
    private function getLifeTimeValue()
    {
        return $this
            ->getCacheLifetime()
            ->getLifetime(CacheLifetime::CACHE_COMPETITION);
    }

    /**
     * @param $type
     * @return CompetitionType|object|null
     */
    private function getCompetitionType($type)
    {
        return $this
            ->getDoctrine()
            ->getRepository(CompetitionType::class)
            ->findOneBy(['name' => $type]);
    }

}
