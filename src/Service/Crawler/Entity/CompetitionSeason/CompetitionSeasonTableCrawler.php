<?php

namespace App\Service\Crawler\Entity\CompetitionSeason;

use App\Entity\CompetitionSeason;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Tool\TransferMkt\CompetitionTableTool;

/**
 * Class CompetitionSeasonTableCrawler
 * @package App\Service\Crawler\Entity\CompetitionSeason
 */
class CompetitionSeasonTableCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var CompetitionSeason[]
     */
    private $seasons = [];

    /**
     * @return CompetitionSeason[]
     */
    public function getSeasons(): array
    {
        return $this->seasons;
    }

    /**
     * @param CompetitionSeason[] $seasons
     * @return CompetitionSeasonTableCrawler
     */
    public function setSeasons(array $seasons): CompetitionSeasonTableCrawler
    {
        $this->seasons = $seasons;
        return $this;
    }

    /**
     * @return CrawlerInterface
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $competitionTableSchema = $this->getConfigSchema('competition.table.collection.url');
        $seasons = $this->getCompetitionSeasons();
        foreach ($seasons as $competitionSeason) {
            $tmkCode = $competitionSeason->getCompetition()->getCode();
            $slug = $competitionSeason->getCompetition()->getSlug();
            $year = $competitionSeason->getStartSeason()->format('YYYY');
            $url = $this->preparePath($competitionTableSchema->getUrl(), [$slug, $tmkCode, $year]);
            $this
                ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION_TABLE))
                ->processPath($url)
            ;

            $rows = CompetitionTableTool::getTableRows($this->getCrawler());

        }
        return $this;
    }

    /**
     *
     */
    public function getData()
    {
        // TODO: Implement getData() method.
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        // TODO: Implement saveData() method.
        return $this;
    }

    /**
     * @return CompetitionSeason[]
     */
    private function getCompetitionSeasons(): array
    {
        if (!empty($this->getSeasons())) {
            return $this->getSeasons();
        }
        return $this->getDoctrine()
            ->getRepository(CompetitionSeason::class)
            ->findAll();
    }

}
