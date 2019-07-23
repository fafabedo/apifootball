<?php


namespace App\Service\Crawler\Entity\Team;


use App\Entity\Competition;
use App\Entity\Country;
use App\Entity\Team;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\Team\TeamOverviewTool;
use App\Tool\TypeTool;
use App\Tool\UrlTool;

/**
 * Class TeamByCodeCrawler
 * @package App\Service\Crawler\Entity\Team
 */
class TeamByCodeCrawler extends ContentCrawler
{
    const TEAM_FOLDER = 'team';
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function process(): CrawlerInterface
    {
        $lifetime = $this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_TEAM);
        if ($this->getTmkCode() === null) {
            return $this;
        }
        $teamOverview = $this->getConfigSchema('crawler.team.overview.page.url');
        $this->createProgressBar('Crawling team overview page', 2);
        $preparedUrl = $this->preparePath($teamOverview->getUrl(), ['team', $this->getTmkCode()]);
        $this
            ->setLifetime($lifetime)
            ->processPath($preparedUrl);

        $team = $this
            ->getDoctrine()
            ->getRepository(Team::class)
            ->findOneByTmkCode($this->getTmkCode());
        if (!$team instanceof Team) {
            $team = new Team();
            $team->setTmkCode($this->getTmkCode());
        }
        $name = TeamOverviewTool::getFullName($this->getCrawler());
        $competitionLink = TeamOverviewTool::getCompetitionLink($this->getCrawler());
        $imageUrl = TeamOverviewTool::getTeamImage($this->getCrawler());
        $competitionTmkCode = UrlTool::getParamFromUrl($competitionLink, 3);
        $competition = $this
            ->getDoctrine()
            ->getRepository(Competition::class)
            ->findOneByTmkCode($competitionTmkCode);

        $filename = $this
            ->processImageUrl($imageUrl, $this->getTmkCode(), self::TEAM_FOLDER);

        if ($competition instanceof Competition) {
            $team->setCountry($competition->getCountry());
        }
        $team->setName($name);
        $team->setShortname($name);
        if (isset($filename)) {
            $team->setImage($filename);
        }
        $team->setTeamType(TypeTool::getClubTypeTeam($this->getDoctrine()));
        $team->setIsYouthTeam( false);
        $metadataSchema = new MetadataSchemaResources();
        $metadataSchema->setUrl($preparedUrl);
        $team->setMetadata($metadataSchema->getSchema());
        $this->team = $team;
        return $this;
    }

    /**
     * @return Team
     */
    public function getData()
    {
        return $this->team;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($this->team);
        $em->flush();
        return $this;
    }

}
