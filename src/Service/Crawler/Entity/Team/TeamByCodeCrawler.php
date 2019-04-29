<?php


namespace App\Service\Crawler\Entity\Team;


use App\Entity\Country;
use App\Entity\Team;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionMainPageTool;
use App\Tool\TransferMkt\Team\TeamOverviewTool;
use App\Tool\TypeTool;

class TeamByCodeCrawler extends ContentCrawler
{
    /**
     * @var Team
     */
    private $team;

    /**
     * @var string
     */
    private $tmkCode;

    /**
     * @return string
     */
    public function getTmkCode(): string
    {
        return $this->tmkCode;
    }

    /**
     * @param string $tmkCode
     * @return TeamByCodeCrawler
     */
    public function setTmkCode(string $tmkCode): TeamByCodeCrawler
    {
        $this->tmkCode = $tmkCode;
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
        if ($this->getTmkCode() === null) {
            return $this;
        }
        $teamOverview = $this->getConfigSchema('team.overview.page.url');
        $this->createProgressBar('Crawling team overview page', 2);
        $url = $this->preparePath($teamOverview->getUrl(), ['team', $this->getTmkCode()]);
        $this
            ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_TEAM))
            ->processPath($url);

        $team = $this
            ->getDoctrine()
            ->getRepository(Team::class)
            ->findOneBy(['tmk_code'=> $this->getTmkCode()]);
        if (!$team instanceof Team) {
            $team = new Team();
            $team->setTmkCode($this->getTmkCode());
        }
        $name = TeamOverviewTool::getFullName($this->getCrawler());
        $competitionLink = TeamOverviewTool::getCompetitionLink($this->getCrawler());

        $team->setCountry($this->getCompetition()->getCountry());
        $team->setName($name);
        $team->setShortname($name);
        $team->setTeamType(TypeTool::getClubTypeTeam($this->getDoctrine()));
        $team->setIsYouthTeam( false);
        $metadataSchema = new MetadataSchemaResources();
        $metadataSchema->setUrl($url);
        $team->setMetadata($metadataSchema->getSchema());

        $this->team = $team;

        return $this;

    }

    public function getData()
    {
        // TODO: Implement getData() method.
    }

    public function saveData(): CrawlerInterface
    {
        // TODO: Implement saveData() method.
    }

}