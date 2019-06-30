<?php

namespace App\Service\Crawler\Entity\Competition;

use App\Entity\Competition;
use App\Entity\CompetitionType;
use App\Entity\TeamType;
use App\Service\Crawler\Item\EntityElement;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionAmericaTool;
use App\Tool\TransferMkt\CompetitionEuropeTool;
use App\Tool\TypeTool;
use App\Tool\UrlTool;

class CompetitionAmericaCrawler extends CompetitionEuropeCrawler implements CrawlerInterface
{
    const COMPETITION_FOLDER = 'competition';

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
        $this->createProgressBar('Crawl America competitions', 2);
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
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getClubCompetitions(): array
    {
        $europeCompetitionSchema = $this
            ->getConfigSchema('crawler.competition.america.collection.url');
        if ($europeCompetitionSchema->getUrl() === null) {
            return [];
        }
        $this
            ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION))
            ->processPath($europeCompetitionSchema->getUrl())
        ;
        $clubComps = CompetitionEuropeTool::getClubCompetitions($this->getCrawler());
        $clubType = TypeTool::getClubTypeTeam($this->getDoctrine());
        $competitions = $this->createCompetitions($clubComps, $clubType);

        return $competitions;
    }

    /**
     * @return array
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getInternationalCompetitions(): array
    {
        $nationalComps = CompetitionEuropeTool::getNationalCompetitions($this->getCrawler());
        $natType = TypeTool::getNationalTypeTeam($this->getDoctrine());
        $competitions = $this->createCompetitions($nationalComps, $natType);
        return $competitions;
    }

    /**
     * @param array $comps
     * @param TeamType $teamType
     * @return array
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function createCompetitions(array $comps, TeamType $teamType): array
    {
        $competitionType = $this
            ->getDoctrine()
            ->getRepository(CompetitionType::class)
            ->find(CompetitionType::TOURNAMENT);
        $competitions = [];
        /* @var EntityElement $item */
        foreach ($comps as $item) {
            $url = $this->getGlobalUrl()->getUrl();
            $url .= $item->getUrl();
            $tmkCode = UrlTool::getParamFromUrl($url, 4);
            $slug = UrlTool::getParamFromUrl($url, 1);

            $competition = $this
                ->getDoctrine()
                ->getRepository(Competition::class)
                ->findOneByTmkCode($tmkCode);
            if (!$competition instanceof Competition) {
                $competition = new Competition();
                $competition->setTmkCode($tmkCode);
                $competition->setSlug($slug);
            }
            $competition->setName($item->getName());
            $competition->setTeamType($teamType);
            $federation = CompetitionAmericaTool::determineFederation($this->getDoctrine(), $item->getName());
            $imageUrl = CompetitionEuropeTool::getImageFromCompetition($this->getCrawler());
            $filename = $this
                ->processImageUrl($imageUrl, $tmkCode, self::COMPETITION_FOLDER);
            $competition->setImage($filename);
            $competition->setFederation($federation);
            $competition->setLeagueLevel(1);
            $competition->setCompetitionType($competitionType);
            $schema = MetadataSchemaResources::createSchema()
                ->setUrl($url);
            $competition->setMetadata($schema->getSchema());

            $competitions[] = $competition;
        }
        return $competitions;
    }
}
