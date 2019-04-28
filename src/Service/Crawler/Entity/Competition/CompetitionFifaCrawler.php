<?php


namespace App\Service\Crawler\Entity\Competition;


use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $this->competitions = $this->getCompetitions();
        $this->processCompetitions();
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
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function processCompetitions(): CrawlerInterface
    {
        if (empty($this->competitions)) {
            return $this;
        }
        $this->createProgressBar('Crawl and process competition', count($this->competitions));
        foreach ($this->competitions as $competition) {
            $schema = MetadataSchemaResources::createSchema($competition->getMetadata());
            if ($schema->getUrl() === null) {
                $this->advanceProgressBar();
                continue;
            }
            $this
                ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION))
                ->processPath($schema->getUrl())
            ;
            $imageUrl = CompetitionEuropeTool::getImageFromCompetition($this->getCrawler());
            $destination = FilesystemTool::getDestination(
                $this->getRootFolder(),
                self::COMPETITION_FOLDER,
                $competition->getCode(),
                FilesystemTool::getExtension($imageUrl)
            );

            $filename = null;
            if (!file_exists($destination)) {
                if (FilesystemTool::persistFile($imageUrl, $destination) === true) {
                    $filename = FilesystemTool::getFilename(self::COMPETITION_FOLDER,
                        $competition->getCode(),
                        FilesystemTool::getExtension($imageUrl));
                }
                $competition->setImage($filename);
            }
            $this->advanceProgressBar();
        }
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
        foreach ($comps as $item) {
            if (!isset($item['url']) || !isset($item['name'])) {
                continue;
            }
            $url= $this->getGlobalUrl() . $item['url'];
            $code = UrlTool::getParamFromUrl($url, 4);
            $slug = UrlTool::getParamFromUrl($url, 1);
            $competition = $this->getCompetitionByCodeOrSlug($code, $slug);
            if (!$competition instanceof Competition) {
                $competition = new Competition();
                $competition->setCode($code);
                $competition->setSlug($slug);
            }
            $competition->setName($item['name']);
            $teamType = TypeTool::getNationalTypeTeam($this->getDoctrine());
            $competition->setTeamType($teamType);
            $competition->setLeagueLevel(1);
            $fifaFederation = FederationTool::getFifaFederation($this->getDoctrine());
            $competition->setFederation($fifaFederation);
            $schema = MetadataSchemaResources::createSchema()
                ->setUrl($url);
            $competition->setMetadata($schema->getSchema());

            // Competition Season
            $competitionSeason = new CompetitionSeason();
            $competitionSeason->setArchive(false);
            $competition->addCompetitionSeason($competitionSeason);

            $competitions[] = $competition;
        }
        return $competitions;
    }

    /**
     * @param $code
     * @param $slug
     * @return Competition|null
     */
    private function getCompetitionByCodeOrSlug($code, $slug): ?Competition
    {
        $competitionSlug = $this
            ->getDoctrine()
            ->getRepository(Competition::class)
            ->findOneBy(['slug' => $slug]);

        if ($competitionSlug instanceof Competition
            && $competitionSlug->getCode() === $code) {
            return $competitionSlug;
        }

        $competitionCode = $this
            ->getDoctrine()
            ->getRepository(Competition::class)
            ->findOneBy(['code' => $code]);

        if ($competitionCode instanceof Competition) {
            return $competitionCode;
        }
        return null;
    }

    /**
     * @return string|null
     * @throws \App\Exception\InvalidMetadataSchema
     */
    private function getCompetitionsFifaUrl(): ?string
    {
        $schema = $this
            ->getConfigSchema('competition.fifa.collection.url');
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
            ->getConfigSchema('global.url');
        if ($schema->getUrl() === null) {
            return null;
        }
        return $schema->getUrl();
    }

}
