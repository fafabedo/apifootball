<?php

namespace App\Service\Crawler\Entity\CompetitionSeason;

use App\Entity\Competition;
use App\Entity\CompetitionSeasonTeam;
use App\Entity\CompetitionSeasonTeamPlayer;
use App\Entity\Player;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\Team\TeamSquadTool;

class CompetitionSeasonPlayerCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var Competition
     */
    private $competition;

    /**
     * @var CompetitionSeasonTeamPlayer[]
     */
    private $competitionSeasonTeamPlayer = [];

    /**
     * @return Competition
     */
    public function getCompetition(): Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     * @return CompetitionSeasonPlayerCrawler
     */
    public function setCompetition(Competition $competition): CompetitionSeasonPlayerCrawler
    {
        $this->competition = $competition;

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
        $seasonTeams = $this->getTeams();
        $playerCollection = $this
            ->getConfigSchema('crawler.competition.player.collection.url');

        $globalUrl = $this->getConfigSchema('crawler.global.url');

        $this->createProgressBar('Crawl players from teams...', count($seasonTeams));

        foreach ($seasonTeams as $seasonTeam) {
            $seasonStart = $seasonTeam->getCompetitionSeason()->getStartSeason();
            if (!$seasonStart instanceof \DateTime) {
                $seasonStart = new \DateTime();
                $timestamp = strtotime('1 year ago');
                $seasonStart->setTimestamp($timestamp);
            }
            $season = $seasonStart->format('Y');
            $team = $seasonTeam->getTeam();

            $slug = 'slug';
            $tmkCode = $team->getTmkCode();
            $preparedUrl = $this->preparePath($playerCollection->getUrl(), [$slug, $tmkCode, $season]);
            $this->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION_SEASON))
                ->processPath($preparedUrl);

            $tablePlayers = TeamSquadTool::getPlayersRows($this->getCrawler());
            foreach ($tablePlayers as $rowPlayer) {
                $player = $this->getDoctrine()
                    ->getRepository(Player::class)
                    ->findOneByTmkCode($rowPlayer['tmkCode']);
                if (!$player instanceof Player) {
                    $player = new Player();
                    $player->setTmkCode($rowPlayer['tmkCode']);
                }
                $player->setName($rowPlayer['name']);
                $schema = MetadataSchemaResources::createSchema();
                $schema->setUrl($globalUrl->getUrl() . $rowPlayer['url']);
                $player->setMetadata($schema->getSchema());

                $competitionSeasonTeamPlayer = $this->getDoctrine()
                    ->getRepository(CompetitionSeasonTeamPlayer::class)
                    ->findOneByPlayerAndTeam($seasonTeam, $player);
                if (!$competitionSeasonTeamPlayer instanceof CompetitionSeasonTeamPlayer) {
                    $competitionSeasonTeamPlayer = new CompetitionSeasonTeamPlayer();
                    $competitionSeasonTeamPlayer->setCompetitionSeasonTeam($seasonTeam);
                    $competitionSeasonTeamPlayer->setPlayer($player);
                }
                $this->competitionSeasonTeamPlayer[] = $competitionSeasonTeamPlayer;
            }
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();
        return $this;
    }

    public function getData()
    {
        return $this->competitionSeasonTeamPlayer;
    }

    public function saveData(): CrawlerInterface
    {
        $em = $this->getDoctrine()->getManager();
        if (empty($this->competitionSeasonTeamPlayer)) {
            return $this;
        }
        $this->createProgressBar('Saving players...', count($this->competitionSeasonTeamPlayer));
        $i = 0;
        foreach ($this->competitionSeasonTeamPlayer as $seasonTeamPlayer) {
            $player = $seasonTeamPlayer->getPlayer();
            $em->persist($player);
            $i++;
            if ($i%100 === 0) {
                $em->flush();
            }
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();

        $this->createProgressBar('Saving players in season...', count($this->competitionSeasonTeamPlayer));
        $i = 0;
        foreach ($this->competitionSeasonTeamPlayer as $seasonTeamPlayer) {
            $em->persist($seasonTeamPlayer);
            $i++;
            if ($i%100 === 0) {
                $em->flush();
            }
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @return CompetitionSeasonTeam[]|null
     */
    private function getTeams()
    {
        if ($this->getCompetition() instanceof Competition) {
            $competition = $this->getCompetition();
            return $this->getDoctrine()
                ->getRepository(CompetitionSeasonTeam::class)
                ->findByCompetition($competition);
            }
        return null;
    }

}
