<?php

namespace App\Service\Crawler\Entity\Competition;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\TeamType;
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
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Doctrine\ORM\NonUniqueResultException
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
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
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
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getClubCompetitions(): array
    {
        $europeCompetitionsUrl = $this->getCompetitionsEuropeUrl();
        if ($europeCompetitionsUrl === null) {
            return [];
        }
        $this
            ->setLifetime($this->getLifeTimeValue())
            ->processPath($europeCompetitionsUrl->getUrl())
        ;
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
        /* @var EntityElement $item */
        foreach ($comps as $item) {
            $url = $this->getGlobalUrl()->getUrl() . $item->getUrl();
            list($tmkCode, $slug) = $this->getTmkCodeThenSlug($url, 4, 1);
            $competition = $this->getDoctrine()
                ->getRepository(Competition::class)
                ->findOneByTmkCode($tmkCode);
            if (!$competition instanceof Competition) {
                $competition = new Competition();
                $competition->setTmkCode($tmkCode);
                $competition->setSlug($slug);
            }
            $competition->setName($item->getName());
            $competition->setTeamType($teamType);
            $uefaFederation = FederationTool::getUefaFederation($this->getDoctrine());
            $competition->setFederation($uefaFederation);
            $competition->setLeagueLevel(1);
            $schema = MetadataSchemaResources::createSchema()
                ->setUrl($url);
            $competition->setMetadata($schema->getSchema());

            $competitions[] = $competition;
        }
        return $competitions;
    }

    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processCompetitions(): CrawlerInterface
    {
        if (empty($this->competitions)) {
            return $this;
        }
        $this->createProgressBar('Process competitions', count($this->competitions));
        foreach ($this->competitions as $competition) {
            $metadata = $competition->getMetadata();
            $tmkCode = $competition->getTmkCode();
            $schema = MetadataSchemaResources::createSchema($metadata);
            if ($schema->getUrl() === null) {
                continue;
            }
            $this
                ->setLifetime($this->getLifeTimeValue())
                ->processPath($schema->getUrl())
            ;
            $teams = CompetitionEuropeTool::getParticipants($this->getCrawler());

            $imageUrl = CompetitionEuropeTool::getImageFromCompetition($this->getCrawler());
            $filename = $this
                ->processImageUrl($imageUrl, $tmkCode, self::COMPETITION_FOLDER);
            $competition->setImage($filename);
            $competition->setNumberTeams($teams);
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @return string|null
     * @throws \App\Exception\InvalidMetadataSchema
     */
    private function getCompetitionsEuropeUrl(): ?MetadataSchemaResources
    {
        return $this
            ->getConfigSchema('crawler.competition.europe.collection.url');
    }

    /**
     * @return string|null
     * @throws \App\Exception\InvalidMetadataSchema
     */
    protected function getGlobalUrl(): ?MetadataSchemaResources
    {
        return $this
            ->getConfigSchema('crawler.global.url');
    }

    private function getLifeTimeValue()
    {
        return $this
            ->getCacheLifetime()
            ->getLifetime(CacheLifetime::CACHE_COMPETITION);
    }

}
