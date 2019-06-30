<?php

namespace App\Service\Crawler\Entity\CompetitionSeason;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonMatchTeam;
use App\Entity\CompetitionSeasonTeam;
use App\Entity\MatchStage;
use App\Entity\Team;
use App\Event\CompetitionSeasonMatchEvent;
use App\Service\Cache\CacheLifetime;
use App\Service\Config\ConfigManager;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Crawler\Entity\Team\TeamByCodeCrawler;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionFixtureTool;
use App\Tool\DateTimeTool;
use App\Tool\TransferMkt\CompetitionGroupsMatchDayTool;
use App\Tool\UrlTool;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CompetitionSeasonMatchCrawler
 * @package App\Service\Crawler\Entity\CompetitionSeason
 */
class CompetitionSeasonMatchCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var CompetitionSeasonMatch[]
     */
    private $competitionMatches = [];

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Competition
     */
    private $competition;

    /**
     * @var CompetitionSeason
     */
    private $competitionSeason;

    /**
     * @var bool
     */
    private $featured = false;

    /**
     * @var CompetitionSeason[]
     */
    private $seasons = [];

    /**
     * @var bool
     */
    private $forceUpdate = false;

    /**
     * @return Competition/null
     */
    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     * @return CompetitionSeasonMatchCrawler
     */
    public function setCompetition(Competition $competition): CompetitionSeasonMatchCrawler
    {
        $this->competition = $competition;

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
     * @return CompetitionSeasonMatchCrawler
     */
    public function setFeatured(bool $featured): CompetitionSeasonMatchCrawler
    {
        $this->featured = $featured;
        return $this;
    }

    /**
     * @return CompetitionSeason
     */
    public function getCompetitionSeason(): ?CompetitionSeason
    {
        return $this->competitionSeason;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     */
    public function setCompetitionSeason(CompetitionSeason $competitionSeason): void
    {
        $this->competitionSeason = $competitionSeason;
    }

    /**
     * @return bool
     */
    public function isForceUpdate(): bool
    {
        return $this->forceUpdate;
    }

    /**
     * @param bool $forceUpdate
     * @return CompetitionSeasonMatchCrawler
     */
    public function setForceUpdate(bool $forceUpdate): CompetitionSeasonMatchCrawler
    {
        $this->forceUpdate = $forceUpdate;

        return $this;
    }

    /**
     * @return CrawlerInterface
     * @throws EntityNotFoundException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function process(): CrawlerInterface
    {
        $fixtureCollection = $this
            ->getConfigSchema('crawler.competition.standard.fixture.collection.url');
        $fixtureCollectionTournament = $this
            ->getConfigSchema('crawler.competition.tournament.fixture.collection.url');

        $competitionSeasons = $this
            ->getDoctrine()
            ->getRepository(CompetitionSeason::class)
            ->findByConfiguration($this->getCompetition(), $this->getCompetitionSeason(), $this->isFeatured());

        $lifetime = $this
            ->getCacheLifetime()
            ->getLifetime(CacheLifetime::CACHE_COMPETITION_MATCH);
        $this->createProgressBar('Crawl fixture information...', count($competitionSeasons));
        $index = 1;
        /* @var CompetitionSeason $competitionSeason */
        foreach ($competitionSeasons as $competitionSeason) {
            if (!$this->validOffset($index)) {
                $index++;
                continue;
            }
            $slug = $competitionSeason->getCompetition()->getSlug();
            $tmkCode = $competitionSeason
                ->getCompetition()
                ->getTmkCode();
            try {
                $today = (new \DateTime('now -1 year'));
                if ($competitionSeason->getStartSeason() instanceof \DateTime) {
                    $today = $competitionSeason->getStartSeason();

                }
                $year = $today->format('Y');
                $competitionTypeId = $competitionSeason
                    ->getCompetition()
                    ->getCompetitionType()
                    ->getId();
                $url = $competitionTypeId === 1 ? $fixtureCollectionTournament->getUrl() : $fixtureCollection->getUrl();
                $preparedUrl = $this->preparePath($url, [$slug, $tmkCode, $year]);
                $this->setLifetime($lifetime)
                    ->processPath($preparedUrl);
                switch ($competitionTypeId) {
                    case 1:
                        $this->competitionMatches = $this->processFixtureTournamentHtml($competitionSeason);
                        break;
                    default:
                    case 2:
                        $this->competitionMatches = $this->processFixtureLeagueHtml($competitionSeason);
                        break;
                }
            } catch (\Exception $e) {
                throw new \Exception('Competition code ('.$tmkCode.'):'.$e->getMessage());
            }
            $this->advanceProgressBar();
            $index++;
            if (count($competitionSeasons) <= $index) {
                $this->setIsCompleted(true);
            }
        }
        $this->finishProgressBar();
        return $this;
    }

    /**
     *
     */
    public function getData(): array
    {
        return $this->competitionMatches;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        $em = $this
            ->getDoctrine()
            ->getManager();

        if (empty($this->competitionMatches)) {
            return $this;
        }

        $this->createProgressBar('Saving matches information', count($this->competitionMatches));
        foreach ($this->competitionMatches as $fixture) {
            $em->persist($fixture);
            $this->advanceProgressBar();
        }
        $em->flush();

        $this->advanceProgressBar();
        $this->finishProgressBar();

        $event = new CompetitionSeasonMatchEvent();
        $event->setCompetitionMatches($this->competitionMatches);
        $this
            ->getEventDispatcher()
            ->dispatch(CompetitionSeasonMatchEvent::POST_UPDATE, $event);

        return $this;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return array
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws EntityNotFoundException
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function processFixtureLeagueHtml(CompetitionSeason $competitionSeason)
    {
        $globalUrl = $this->getConfigSchema('crawler.global.url');
        $tablesNode = CompetitionFixtureTool::getLeagueTableNodes($this->getCrawler());

        $matchStage = $this->getMatchStage(MatchStage::MATCH_STAGE_LEAGUE);

        $fixtureMatches = [];
        $matchDay = 1;
        foreach ($tablesNode as $table) {
            /* @var Crawler $row */
            foreach ($table as $row) {
                try {
                    $cells = $row->children(); // TDs in Table
                    $cellDate = $cells->eq(0)->html();
                    $cellTime = $cells->eq(1)->text();
                    $cellMatch = $cells->eq(4)->html();
                    $cellHome = $cells->eq(3)->html();
                    $cellAway = $cells->eq(5)->html();
                    $matchDate = CompetitionFixtureTool::extractDateCellDate($cellDate);
                    $matchTime = DateTimeTool::setTextTimeToDateTime($matchDate, $cellTime);
                    $path = CompetitionFixtureTool::extractMatchLink($cellMatch);
                    $homeTmkCode = CompetitionFixtureTool::extractTeamCode($cellHome);
                    $awayTmkCode = CompetitionFixtureTool::extractTeamCode($cellAway);
                    $url = $globalUrl->getUrl().$path;
                    $tmkCode = UrlTool::getParamFromUrl($url, 4);

                    $match = $this->findFixtureByTmkCode($tmkCode);
                    if (!$match instanceof CompetitionSeasonMatch) {
                        $match = new CompetitionSeasonMatch();
                        $match->setTmkCode($tmkCode);
                    }
                    if (!$this->matchShouldBeUpdated($match)) {
                        continue;
                    }
                    $match->setCompetitionSeason($competitionSeason);
                    $match->setMatchDatetime($matchTime);
                    $match->setMatchDay($matchDay);
                    $match->setMatchStage($matchStage);
                    $schema = (MetadataSchemaResources::createSchema())->setUrl($url);
                    $match->setMetadata($schema->getSchema());

                    $match = $this->addCompetitionSeasonMatchTeam($match, $homeTmkCode, $cellMatch);
                    $match = $this->addCompetitionSeasonMatchTeam($match, $awayTmkCode, $cellMatch, false);
                    $fixtureMatches[] = $match;
                } catch (EntityNotFoundException $e) {
                    $this->getOutput()->writeln('error found');
                    $this->errors[] = [
                        'tmk_code' => $homeTmkCode,
                        'tmk_code_1' => $awayTmkCode,
                        'season' => $competitionSeason->getId(),
                        'match_day' => $matchDay,
                    ];
                    continue;
                }
            }
            $matchDay++;
        }

        return $fixtureMatches;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return array
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function processFixtureTournamentHtml(CompetitionSeason $competitionSeason)
    {
        $globalUrl = $this->getConfigSchema('crawler.global.url');
        $tablesNode = CompetitionGroupsMatchDayTool::getTournamentBoxNodes($this->getCrawler());
        $matchSGroup = $this->getMatchStage('Group');

        $fixtureMatches = [];
        $matchDay = 1;
        foreach ($tablesNode as $box) {
            $boxNode = new Crawler();
            $boxNode->addNode($box);
            $type = CompetitionGroupsMatchDayTool::getTypeTournamentBox($boxNode);
            $rows = null;
            switch ($type) {
                case 'knockout':
                    $rows = CompetitionGroupsMatchDayTool::getKnockoutRowsMatches($boxNode);
                    break;
                case 'group':
                    $rows = CompetitionGroupsMatchDayTool::getGroupRowsMatches($boxNode);
                    break;
            }
            if (empty($rows)) {
                continue;
            }

            switch ($type) {
                case 'knockout':
                    /* @var Crawler $row */
                    foreach ($rows as $section => $items) {
                        foreach ($items as $row) {
                            $match = $this->createMatchFromTournamentKnockoutRow(
                                $row,
                                $competitionSeason,
                                $globalUrl->getUrl()
                            );
                            $matchStage = $this->getMatchStage($section);
                            $match->setMatchStage($matchStage);
                            $fixtureMatches[] = $match;
                        }
                    }
                    break;
                case 'group':
                default:
                    /* @var Crawler $row */
                    foreach ($rows as $row) {
                        $groupName = CompetitionGroupsMatchDayTool::getGroupNameFromTable($boxNode);
                        $match = $this->createMatchFromTournamentGroupsRow(
                            $row,
                            $competitionSeason,
                            $globalUrl->getUrl()
                        );
                        $match->setMatchGroup($groupName);
                        $match->setMatchStage($matchSGroup);

                        $seasonMatchTeams = $match->getCompetitionSeasonMatchTeams();
                        foreach ($seasonMatchTeams as $seasonMatchTeam) {
                            $team = $seasonMatchTeam->getTeam();
                            $competitionSeasonTeam = $competitionSeason->getCompetitionSeasonTeams()->filter(
                                function (CompetitionSeasonTeam $competitionSeasonTeam) use ($team) {
                                    return ($competitionSeasonTeam->getTeam()->getId() === $team->getId());
                                }
                            )->first();
                            /* @var CompetitionSeasonTeam $competitionSeasonTeam */
                            $competitionSeasonTeam->setGroupName($groupName);
                        }
                        $fixtureMatches[] = $match;
                    }
                    break;
            }
            $matchDay++;
        }

        return $fixtureMatches;
    }

    /**
     * @param Crawler $node
     * @param CompetitionSeason $competitionSeason
     * @param $globalUrl
     * @return CompetitionSeasonMatch|null
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function createMatchFromTournamentGroupsRow(
        Crawler $node,
        CompetitionSeason $competitionSeason,
        $globalUrl
    ) {
        $cells = $node->filter('td');
        $cellDate = $cells->eq(0)->text();
        $cellMatch = $cells->eq(3)->html();
        $cellHome = $cells->eq(1)->html();
        $cellAway = $cells->eq(5)->html();
        $matchDate = DateTimeTool::createDateTime($cellDate);
        $matchTime = DateTimeTool::setTextTimeToDateTime($matchDate, '12:00 PM');
        $path = CompetitionFixtureTool::extractMatchLink($cellMatch);
        $homeTmkCode = CompetitionFixtureTool::extractTeamCode($cellHome);
        $awayTmkCode = CompetitionFixtureTool::extractTeamCode($cellAway);
        $url = $globalUrl.$path;
        $tmkCode = UrlTool::getParamFromUrl($url, 4);

        $match = $this->findFixtureByTmkCode($tmkCode);
        if (!$match instanceof CompetitionSeasonMatch) {
            $match = new CompetitionSeasonMatch();
            $match->setTmkCode($tmkCode);
        }
        if (!$this->matchShouldBeUpdated($match)) {
            return $match;
        }
        $match->setCompetitionSeason($competitionSeason);
        $match->setMatchDatetime($matchTime);
        $schema = (MetadataSchemaResources::createSchema())->setUrl($url);
        $match->setMetadata($schema->getSchema());

        try {
            $match = $this->addCompetitionSeasonMatchTeam($match, $homeTmkCode, $cellMatch);
            $match = $this->addCompetitionSeasonMatchTeam($match, $awayTmkCode, $cellMatch, false);
            $fixtureMatches[] = $match;
        } catch (EntityNotFoundException $e) {
            $this->errors[] = [
                'tmk_code' => $homeTmkCode,
                'tmk_code_1' => $awayTmkCode,
                'season' => $competitionSeason->getId(),
            ];
        }

        return $match;
    }

    /**
     * @param Crawler $node
     * @param CompetitionSeason $competitionSeason
     * @param $globalUrl
     * @return CompetitionSeasonMatch|null
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function createMatchFromTournamentKnockoutRow(
        Crawler $node,
        CompetitionSeason $competitionSeason,
        $globalUrl
    ) {
        $cells = $node->filter('td');
        $cellDate = $cells->eq(0)->html();
        $cellTime = $cells->eq(1)->text();
        $cellMatch = $cells->eq(4)->html();
        $cellHome = $cells->eq(3)->html();
        $cellAway = $cells->eq(5)->html();
        $matchDate = DateTimeTool::createDateTime($cellDate);
        $matchTime = DateTimeTool::setTextTimeToDateTime($matchDate, $cellTime);
        $path = CompetitionFixtureTool::extractMatchLink($cellMatch);
        $homeTmkCode = CompetitionFixtureTool::extractTeamCode($cellHome);
        $awayTmkCode = CompetitionFixtureTool::extractTeamCode($cellAway);
        $url = $globalUrl.$path;
        $tmkCode = UrlTool::getParamFromUrl($url, 4);

        $match = $this->findFixtureByTmkCode($tmkCode);
        if (!$match instanceof CompetitionSeasonMatch) {
            $match = new CompetitionSeasonMatch();
            $match->setTmkCode($tmkCode);
        }
        if (!$this->matchShouldBeUpdated($match)) {
            return $match;
        }
        $match->setCompetitionSeason($competitionSeason);
        $match->setMatchDatetime($matchTime);
        $schema = (MetadataSchemaResources::createSchema())->setUrl($url);
        $match->setMetadata($schema->getSchema());

        try {
            $match = $this->addCompetitionSeasonMatchTeam($match, $homeTmkCode, $cellMatch);
            $match = $this->addCompetitionSeasonMatchTeam($match, $awayTmkCode, $cellMatch, false);
            $fixtureMatches[] = $match;
        } catch (EntityNotFoundException $e) {
            $this->errors[] = [
                'tmk_code' => $homeTmkCode,
                'tmk_code_1' => $awayTmkCode,
                'season' => $competitionSeason->getId(),
            ];
        }

        return $match;
    }

    /**
     * @param CompetitionSeasonMatch $match
     * @return bool
     */
    private function matchShouldBeUpdated(CompetitionSeasonMatch $match): bool
    {
        if ($this->isForceUpdate()) {
            return true;
        }

        return ($match->getIsProcessed() === true ? false : true);
    }

    /**
     * @param $tmkCode
     * @return CompetitionSeasonMatch|object|null
     */
    private function findFixtureByTmkCode($tmkCode)
    {
        $result = $this->getDoctrine()
            ->getRepository(CompetitionSeasonMatch::class)
            ->findByTmkCode($tmkCode);
        if (count($result) > 1) {
            $this->getOutput()->writeln("Many fixture matches have been found for $tmkCode code");
        }

        return $this->getDoctrine()
            ->getRepository(CompetitionSeasonMatch::class)
            ->findOneByTmkCode($tmkCode);
    }

    /**
     * @param CompetitionSeasonMatch $competitionSeasonMatch
     * @param $tmkCode
     * @param $cellMatch
     * @param bool $home
     * @return CompetitionSeasonMatch
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function addCompetitionSeasonMatchTeam(
        CompetitionSeasonMatch $competitionSeasonMatch,
        $tmkCode,
        $cellMatch,
        $home = true
    ): CompetitionSeasonMatch {
        $team = $this
            ->getDoctrine()
            ->getRepository(Team::class)
            ->findOneByTmkCode($tmkCode);
        if (!$team instanceof Team) {
            $this->getTeamByCodeCrawler()
                ->setTmkCode($tmkCode)
                ->process()
                ->saveData();

            $team = $this
                ->getDoctrine()
                ->getRepository(Team::class)
                ->findOneByTmkCode($tmkCode);
        }

        $this->addTeamToCollection($competitionSeasonMatch->getCompetitionSeason(), $team);
        $result = $competitionSeasonMatch
            ->getCompetitionSeasonMatchTeams()
            ->filter(
                function (CompetitionSeasonMatchTeam $competitionSFTeam) use ($team) {
                    return ($competitionSFTeam->getTeam()->getId() === $team->getId());
                }
            );
        $fixtureTeam = null;
        if ($result->count() > 0) {
            $fixtureTeam = $result->first();
        }
        $scoreHome = CompetitionFixtureTool::extractScore($cellMatch, true);
        $scoreAway = CompetitionFixtureTool::extractScore($cellMatch, false);

        if (!$fixtureTeam instanceof CompetitionSeasonMatchTeam) {
            $fixtureTeam = new CompetitionSeasonMatchTeam();
            $fixtureTeam->setTeam($team);
        }
        $fixtureTeam->setIsHome($home);
        if ($scoreHome !== null && $scoreAway !== null) {
            $fixtureTeam->setScore($home ? $scoreHome : $scoreAway);
            $fixtureTeam->setIsVictory($this->isVictory($home, $scoreHome, $scoreAway));
            $fixtureTeam->setIsDraw(($scoreHome === $scoreAway));
            $competitionSeasonMatch->setIsPlayed(true);
        }
        $competitionSeasonMatch->setIsProcessed(true);
        $competitionSeasonMatch->addCompetitionSeasonMatchTeam($fixtureTeam);

        return $competitionSeasonMatch;
    }

    /**
     * @param bool $home
     * @param int $scoreHome
     * @param int $scoreAway
     * @return bool
     */
    private function isVictory($home = true, $scoreHome = 0, $scoreAway = 0)
    {
        switch (true) {
            case ($home && $scoreHome > $scoreAway) :
            case (!$home && $scoreHome < $scoreAway) :
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * @param $stageName
     * @return MatchStage|null
     */
    private function getMatchStage($stageName): ?MatchStage
    {
        return $this->getDoctrine()
            ->getRepository(MatchStage::class)
            ->findOneBy(['name' => $stageName]);
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @param Team $team
     * @param null $groupName
     * @return CompetitionSeasonMatchCrawler
     */
    private function addTeamToCollection(
        CompetitionSeason $competitionSeason,
        Team $team,
        $groupName = null
    ): CompetitionSeasonMatchCrawler {
        $competitionSeasonTeam = $competitionSeason->getCompetitionSeasonTeams()->filter(
            function (CompetitionSeasonTeam $competitionSeasonTeam) use ($team) {
                return ($competitionSeasonTeam->getTeam()->getId() === $team->getId());
            }
        )->first();
        if (!$competitionSeasonTeam instanceof CompetitionSeasonTeam) {
            $competitionSeasonTeam = new CompetitionSeasonTeam();
            $competitionSeasonTeam->setTeam($team);
            if (isset($groupName)) {
                $competitionSeasonTeam->setGroupName($groupName);
            }
            $competitionSeason->addCompetitionSeasonTeam($competitionSeasonTeam);
        }

        return $this;
    }

    /**
     * @return TeamByCodeCrawler|object
     */
    private function getTeamByCodeCrawler()
    {
        $class = 'App\\Service\\Crawler\\Entity\\Team\\TeamByCodeCrawler';
        $this->getContainer()->initialized($class);
        return $this
            ->getContainer()
            ->get($class);
    }

}
