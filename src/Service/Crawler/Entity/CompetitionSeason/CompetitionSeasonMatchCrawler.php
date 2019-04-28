<?php

namespace App\Service\Crawler\Entity\CompetitionSeason;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonMatchTeam;
use App\Entity\Team;
use App\Event\CompetitionSeasonMatchEvent;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionFixtureTool;
use App\Tool\DateTimeTool;
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
     * @var CompetitionSeasonMatch[]
     */
    private $updatedCompetitionMatches = [];

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Competition
     */
    private $competition;

    /**
     * @var CompetitionSeason[]
     */
    private $seasons = [];

    /**
     * @var bool
     */
    private $archive = false;

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
     * @return CompetitionSeason[]
     */
    public function getSeasons(): array
    {
        return $this->seasons;
    }

    /**
     * @param CompetitionSeason[] $seasons
     * @return CompetitionSeasonMatchCrawler
     */
    public function setSeasons(array $seasons): CompetitionSeasonMatchCrawler
    {
        $this->seasons = $seasons;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArchive(): bool
    {
        return $this->archive;
    }

    /**
     * @param bool $archive
     * @return CompetitionSeasonMatchCrawler
     */
    public function setArchive(bool $archive): CompetitionSeasonMatchCrawler
    {
        $this->archive = $archive;
        return $this;
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
     */
    public function process(): CrawlerInterface
    {
        $fixtureCollection = $this
            ->getConfigSchema('competition.standard.fixture.collection.url');
        $fixtureCollectionTournament = $this
            ->getConfigSchema('competition.tournament.fixture.collection.url');

        $competitionSeasons = $this->getCompetitionSeasons();
        $this->createProgressBar('Crawl fixture information...', count($competitionSeasons));
        foreach ($competitionSeasons as $competitionSeason) {
            $slug = $competitionSeason->getCompetition()->getSlug();
            $code = $competitionSeason->getCompetition()->getCode();
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
            $preparedUrl = $this->preparePath($url, [$slug, $code, $year]);
            $this->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION_MATCH))
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
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();
        if (!empty($this->errors)) {
            $this->getOutput()->writeln('Errors found');
            foreach ($this->errors as $item) {
                $this->getOutput()->writeln('competition season id: ' . $item['season']);
                if (isset($item['match_day'])) {
                    $this->getOutput()->writeln('match_day: ' . $item['match_day']);
                }
                $this->getOutput()->writeln('tmk_code: ' . $item['tmk_code']);
                $this->getOutput()->writeln('tmk_code_1: ' . $item['tmk_code_1']);
            }
        }
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
            ->dispatch(CompetitionSeasonMatchEvent::POST_UPDATE, $event)
        ;

        return $this;
    }

    /**
     * @return CompetitionSeason[]|object[]
     */
    private function getCompetitionSeasons()
    {
        $filters = [];
        if (!$this->isArchive()) {
            $filters['archive'] = $this->isArchive();
        }
        if ($this->getCompetition() instanceof Competition) {
            $filters['competition'] = $this->getCompetition();
        }
        if (!empty($this->getSeasons())) {
            $ids = [];
            foreach ($this->getSeasons() as $competitionSeason) {
                $ids[] = $competitionSeason->getId();
            }
            $filters['id'] = $ids;
        }
        return $this->getDoctrine()
            ->getRepository(CompetitionSeason::class)
            ->findBy($filters);
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return array
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    private function processFixtureLeagueHtml(CompetitionSeason $competitionSeason)
    {
        $globalUrl = $this->getConfigSchema('global.url');
        $tablesNode = CompetitionFixtureTool::getLeagueTableNodes($this->getCrawler());

        $fixtureMatches = [];
        $matchDay = 1;
        foreach ($tablesNode as $table) {
            /* @var Crawler $row */
            foreach ($table as $row) {
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
                $url = $globalUrl->getUrl() . $path;
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
                $schema = (MetadataSchemaResources::createSchema())->setUrl($url);
                $match->setMetadata($schema->getSchema());

                try {
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
     */
    private function processFixtureTournamentHtml(CompetitionSeason $competitionSeason)
    {
        $globalUrl = $this->getConfigSchema('global.url');
        $tablesNode = CompetitionFixtureTool::getTournamentBoxNodes($this->getCrawler());

        $fixtureMatches = [];
        $matchDay = 1;
        foreach ($tablesNode as $box) {
            $boxNode = new Crawler();
            $boxNode->addNode($box);
            $type = CompetitionFixtureTool::getTypeTournamentBox($boxNode);
            $rows = null;
            switch ($type) {
                case 'knockout':
                    break;
                case 'group':
                    $rows = CompetitionFixtureTool::getGroupRowsMatches($boxNode);
                    break;
            }
            if (empty($rows)) {
                continue;
            }
            /* @var Crawler $row */
            foreach ($rows as $row) {
                $groupName = CompetitionFixtureTool::getGroupNameFromTable($boxNode);
                $match = $this->createMatchFromTournamentGroupsRow($row, $competitionSeason, $globalUrl->getUrl());
                $match->setMatchGroup($groupName);

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
     */
    private function createMatchFromTournamentGroupsRow(
        Crawler $node,
        CompetitionSeason $competitionSeason,
        $globalUrl)
    {
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
        $url = $globalUrl . $path;
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
            ->findBy(['tmk_code' => $tmkCode]);
        if (count($result) > 1) {
            $this->getOutput()->writeln("Many fixture matches have been found for $tmkCode code");
        }
        return $this->getDoctrine()
            ->getRepository(CompetitionSeasonMatch::class)
            ->findOneBy(['tmk_code' => $tmkCode]);
    }

    /**
     * @param CompetitionSeasonMatch $competitionSeasonMatch
     * @param $tmkCode
     * @param $cellMatch
     * @param bool $home
     * @return \Doctrine\Common\Collections\Collection
     * @throws EntityNotFoundException
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
            ->findOneBy(['tmk_code' => $tmkCode]);
        if (!$team instanceof Team) {
            throw new EntityNotFoundException("Team with code: $tmkCode not found");
        }

        $result = $competitionSeasonMatch
            ->getCompetitionSeasonMatchTeams()
            ->filter(function (CompetitionSeasonMatchTeam $competitionSFTeam) use ($team) {
                return ($competitionSFTeam->getTeam()->getId() === $team->getId());
            });
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
            $competitionSeasonMatch->setIsProcessed(true);
        }
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
                return true;
                break;
            case (!$home && $scoreHome < $scoreAway) :
                return true;
                break;
            default:
                return false;
                break;
        }
    }

}
