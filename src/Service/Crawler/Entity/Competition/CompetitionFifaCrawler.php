<?php


namespace App\Service\Crawler\Entity\Competition;


use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Crawler\Item\EntityElement;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionEuropeTool;
use App\Tool\FederationTool;
use App\Tool\FilesystemTool;
use App\Tool\TypeTool;
use App\Tool\UrlTool;

/**
 * Class CompetitionFifaCrawler
 * @package App\Service\Entity\Competition
 */
class CompetitionFifaCrawler extends ContentCrawler implements CrawlerInterface
{
    private const COMPETITION_FOLDER = 'competition';
    /**
     * @var array
     */
    private $competitions = [];

    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $competitions = $this->getCompetitions();
        if (empty($competitions)) {
            return $this;
        }
        $lifetime = $this
            ->getCacheLifetime()
            ->getLifetime(CacheLifetime::CACHE_COMPETITION);
        $this->createProgressBar('Crawl and process competition', count($competitions));
        /* @var Competition $competition */
        foreach ($competitions as $competition) {
            $tmkCode = $competition->getTmkCode();
            $schema = MetadataSchemaResources::createSchema($competition->getMetadata());
            if ($schema->getUrl() === null) {
                $this->advanceProgressBar();
                continue;
            }
            $this
                ->setLifetime($lifetime)
                ->processPath($schema->getUrl())
            ;
            try {
                $imageUrl = CompetitionEuropeTool::getImageFromCompetition($this->getCrawler());
                $filename = $this
                    ->processImageUrl($imageUrl, $tmkCode, self::COMPETITION_FOLDER);
                $competition->setImage($filename);
                $this->competitions[] = $competition;
            }
            catch (\Exception $e) {
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
        return $this->competitions;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        $this->createProgressBar('Saving competitions', count($this->competitions));
        $em = $this->getDoctrine()->getManager();
        foreach ($this->competitions as $competition) {
            $em->persist($competition);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @return array
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getCompetitions(): array
    {
        $this->createProgressBar('Retrieve competitions', 2);
        $europeCompetitionsUrl = $this->getCompetitionsFifaUrl();
        if ($europeCompetitionsUrl === null) {
            return [];
        }
        $this
            ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION))
            ->processPath($europeCompetitionsUrl)
        ;
        $this->advanceProgressBar();
        $competitions = CompetitionEuropeTool::getNationalCompetitions($this->getCrawler());
        $competitions = $this->createCompetitions($competitions);
        $this->finishProgressBar();
        return $competitions;
    }

    /**
     * @param array $comps
     * @return array
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \App\Exception\InvalidMetadataSchema
     */
    private function createCompetitions(array $comps): array
    {
        $competitions = [];
        /* @var EntityElement $item */
        foreach ($comps as $item) {
            $url= $this->getGlobalUrl() . $item->getUrl();
            $tmkCode = UrlTool::getParamFromUrl($url, 4);
            $slug = UrlTool::getParamFromUrl($url, 1);
            $competition = $this->getDoctrine()
                ->getRepository(Competition::class)
                ->findOneByTmkCode($tmkCode);
            if (!$competition instanceof Competition) {
                $competition = new Competition();
                $competition->setTmkCode($tmkCode);
                $competition->setSlug($slug);
            }
            $competition->setName($item->getName());
            $teamType = TypeTool::getNationalTypeTeam($this->getDoctrine());
            $competition->setTeamType($teamType);
            $competition->setLeagueLevel(1);
            $fifaFederation = FederationTool::getFifaFederation($this->getDoctrine());
            $competition->setFederation($fifaFederation);
            $schema = MetadataSchemaResources::createSchema()
                ->setUrl($url);
            $competition->setMetadata($schema->getSchema());

            $competitions[] = $competition;
        }
        return $competitions;
    }

    /**
     * @return string|null
     * @throws \App\Exception\InvalidMetadataSchema
     */
    private function getCompetitionsFifaUrl(): ?string
    {
        $schema = $this
            ->getConfigSchema('crawler.competition.fifa.collection.url');
        if ($schema->getUrl() === null) {
            return null;
        }
        return $schema->getUrl();
    }

    /**
     * @return string
     * @throws \App\Exception\InvalidMetadataSchema
     */
    private function getGlobalUrl(): ?string
    {
        $schema = $this
            ->getConfigSchema('crawler.global.url');
        if ($schema->getUrl() === null) {
            return null;
        }
        return $schema->getUrl();
    }

}
