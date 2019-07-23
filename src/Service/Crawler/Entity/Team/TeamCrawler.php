<?php

namespace App\Service\Crawler\Entity\Team;

use App\Entity\Competition;
use App\Entity\CompetitionSeasonTeam;
use App\Entity\Country;
use App\Entity\Team;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionMainPageTool;
use App\Tool\TransferMkt\Team\TeamOverviewTool;
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
    const TEAM_FOLDER = 'team';
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
    private $level = 2;

    /**
     * @var bool
     */
    private $featured = false;

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
     * @return bool
     */
    public function isFeatured(): bool
    {
        return $this->featured;
    }

    /**
     * @param bool $featured
     * @return TeamCrawler
     */
    public function setFeatured(bool $featured): TeamCrawler
    {
        $this->featured = $featured;
        return $this;
    }

    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function process(): CrawlerInterface
    {
        $competitionTeams = $this->getCompetitionTeams();

        $this->createProgressBar('Crawling competitions to scope', count($competitionTeams));

        foreach ($competitionTeams as $competitionTeam) {
            $team = $competitionTeam->getTeam();
            $metadata = MetadataSchemaResources::createSchema($team->getMetadata());
            $preparedUrl = $metadata->getUrl();
            $this
                ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_TEAM))
                ->processPath($preparedUrl);

            $fullName = TeamOverviewTool::getFullName($this->getCrawler());
            $competitionLink = TeamOverviewTool::getCanonical($this->getCrawler());
            $imageUrl = TeamOverviewTool::getTeamImage($this->getCrawler());
            $filename = $this
                ->processImageUrl($imageUrl, $team->getTmkCode(), self::TEAM_FOLDER);
            $team->setImage($filename);
            $team->setName($fullName);
            $metadataSchema = new MetadataSchemaResources();
            $metadataSchema->setUrl($competitionLink);
            $team->setMetadata($metadataSchema->getSchema());
            $this->teams[] = $team;
            $this->advanceProgressBar();
        }

        $this->finishProgressBar();
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
     * @return CompetitionSeasonTeam[]/array
     * @throws \Exception
     */
    public function getCompetitionTeams(): array
    {
        switch (true) {
            case $this->isFeatured():
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonTeam::class)
                    ->findByFeaturedCompetition();
                break;
            case ($this->getCountry() instanceof Country):
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonTeam::class)
                    ->findByCountry($this->getCountry());
                break;
            case ($this->getCompetition() instanceof Competition):
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonTeam::class)
                    ->findByCompetition($this->getCompetition());
                break;
            default:
                return [];
                break;
        }
    }

}
