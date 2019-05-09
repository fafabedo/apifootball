<?php


namespace App\Service\Crawler\Entity\Competition;


use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\TeamType;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionAmericaTool;
use App\Tool\TransferMkt\CompetitionEuropeTool;
use App\Tool\TypeTool;
use App\Tool\UrlTool;

class CompetitionAmericaCrawler extends CompetitionEuropeCrawler implements CrawlerInterface
{
    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     */
    private function getClubCompetitions(): array
    {
        $europeCompetitionSchema = $this
            ->getConfigSchema('competition.america.collection.url');
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
     */
    private function createCompetitions(array $comps, TeamType $teamType): array
    {
        $competitions = [];
        foreach ($comps as $item) {
            if (!isset($item['url']) || !isset($item['name'])) {
                continue;
            }
            $url = $this->getGlobalUrl()->getUrl() . $item['url'];
            $code = UrlTool::getParamFromUrl($url, 4);
            $slug = UrlTool::getParamFromUrl($url, 1);
            if ($code === 'C19A') {
                $tmp = 1;
            }
            $competition = $this->getCompetitionByCodeOrSlug($code, $slug);
            if (!$competition instanceof Competition) {
                $competition = new Competition();
                $competition->setCode($code);
                $competition->setSlug($slug);
            }
            $competition->setName($item['name']);
            $competition->setTeamType($teamType);
            $federation = CompetitionAmericaTool::determineFederation($this->getDoctrine(), $item['name']);
            $competition->setFederation($federation);
            $competition->setLeagueLevel(1);
            $schema = MetadataSchemaResources::createSchema()
                ->setUrl($url);
            $competition->setMetadata($schema->getSchema());

            // Competition Season
            $competitionSeason = new CompetitionSeason();
            $competitionSeason->setArchive(false);
            $competitionSeason->setMetadata($schema->getSchema());
            $competition->addCompetitionSeason($competitionSeason);

            $competitions[] = $competition;
        }
        return $competitions;
    }
}
