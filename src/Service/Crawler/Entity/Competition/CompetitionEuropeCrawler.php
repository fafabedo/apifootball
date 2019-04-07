<?php

namespace App\Service\Crawler\Entity\Competition;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\TeamType;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\CompetitionEuropeTool;
use App\Tool\CompetitionMainPageTool;
use App\Tool\FederationTool;
use App\Tool\FilesystemTool;
use App\Tool\TypeTool;
use App\Tool\UrlTool;

/**
 * Class CompetitionEuropeCrawler
 * @package App\Service\Entity\Competition
 */
class CompetitionEuropeCrawler extends ContentCrawler implements CrawlerInterface
{
    protected const COMPETITION_FOLDER = 'competition';

    /**
     * @var Competition[]
     */
    protected $competitions = [];

    /**
     * @var bool
     */
    protected $club_crawl = true;

    /**
     * @var bool
     */
    protected $international_crawl = true;

    /**
     * @return bool
     */
    public function isClubCrawl(): bool
    {
        return $this->club_crawl;
    }

    /**
     * @param bool $club_crawl
     * @return CompetitionEuropeCrawler
     */
    public function setClubCrawl(bool $club_crawl): CompetitionEuropeCrawler
    {
        $this->club_crawl = $club_crawl;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInternationalCrawl(): bool
    {
        return $this->international_crawl;
    }

    /**
     * @param bool $international_crawl
     * @return CompetitionEuropeCrawler
     */
    public function setInternationalCrawl(bool $international_crawl): CompetitionEuropeCrawler
    {
        $this->international_crawl = $international_crawl;
        return $this;
    }

    /**
     * @return CrawlerInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $this->createProgressBar('Crawl Europe competitions', 2);
        if ($this->isClubCrawl()) {
            $this->competitions = array_merge($this->competitions, $this->getClubCompetitions());
        }
        $this->advanceProgressBar();
        if ($this->isInternationalCrawl()) {
            $this->competitions = array_merge($this->competitions, $this->getInternationalCompetitions());
        }
        $this->advanceProgressBar();
        $this->finishProgressBar();
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
     * @return array
     */
    private function getInternationalCompetitions(): array
    {
        $europeCompetitionsUrl = $this->getCompetitionsEuropeUrl();
        if ($europeCompetitionsUrl === null) {
            return [];
        }
        $nationalComps = CompetitionEuropeTool::getNationalCompetitions($this->getCrawler());
        $natType = TypeTool::getNationalTypeTeam($this->getDoctrine());
        $competitions = $this->createCompetitions($nationalComps, $natType);
        return $competitions;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getClubCompetitions(): array
    {
        $europeCompetitionsUrl = $this->getCompetitionsEuropeUrl();
        if ($europeCompetitionsUrl === null) {
            return [];
        }
        $this->processPath($europeCompetitionsUrl);
        $clubComps = CompetitionEuropeTool::getClubCompetitions($this->getCrawler());
        $clubType = TypeTool::getClubTypeTeam($this->getDoctrine());
        $competitions = $this->createCompetitions($clubComps, $clubType);

        return $competitions;
    }

    /**
     * @param array $comps
     * @param TeamType $teamType
     * @return array
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     */
    private function createCompetitions(array $comps, TeamType $teamType): array
    {
        $competitions = [];
        foreach ($comps as $item) {
            if (!isset($item['url']) || !isset($item['name'])) {
                continue;
            }
            $url = $this->getGlobalUrl() . $item['url'];
            $code = UrlTool::getParamFromUrl($url, 4);
            $slug = UrlTool::getParamFromUrl($url, 1);
            $competition = $this->getCompetitionByCodeOrSlug($code, $slug);
            if (!$competition instanceof Competition) {
                $competition = new Competition();
                $competition->setCode($code);
                $competition->setSlug($slug);
            }
            $competition->setName($item['name']);
            $competition->setTeamType($teamType);
            $uefaFederation = FederationTool::getUefaFederation($this->getDoctrine());
            $competition->setFederation($uefaFederation);
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
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function processCompetitions(): CrawlerInterface
    {
        if (empty($this->competitions)) {
            return $this;
        }
        $this->createProgressBar('Process competitions', count($this->competitions));
        foreach ($this->competitions as $competition) {
            $metadata = $competition->getMetadata();
            $schema = MetadataSchemaResources::createSchema($metadata);
            if ($schema->getUrl() === null) {
                continue;
            }
            $this->processPath($schema->getUrl());
            $imageUrl = CompetitionEuropeTool::getImageFromCompetition($this->getCrawler());
            $teams = CompetitionEuropeTool::getParticipants($this->getCrawler());
            $destination = FilesystemTool::getDestination(
                $this->getRootFolder(),
                self::COMPETITION_FOLDER,
                $competition->getCode(),
                FilesystemTool::getExtension($imageUrl)
            );

            $filename = null;
            if (FilesystemTool::persistFile($imageUrl, $destination) === true) {
                $filename = FilesystemTool::getFilename(self::COMPETITION_FOLDER,
                    $competition->getCode(),
                    FilesystemTool::getExtension($imageUrl));
            }
            $competition->setImage($filename);
            $competition->setNumberTeams($teams);
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @param $code
     * @param $slug
     * @return Competition|null
     */
    protected function getCompetitionByCodeOrSlug($code, $slug): ?Competition
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
    private function getCompetitionsEuropeUrl(): ?string
    {
        return $this
            ->getConfigSchema('competition.europe.collection.url');
    }

    /**
     * @return string|null
     * @throws \App\Exception\InvalidMetadataSchema
     */
    protected function getGlobalUrl(): ?string
    {
        return $this
            ->getConfigSchema('global.url');
    }

}
