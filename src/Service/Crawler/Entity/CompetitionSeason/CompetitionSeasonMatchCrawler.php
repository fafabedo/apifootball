<?php

namespace App\Service\Crawler\Entity\CompetitionSeason;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonMatchTeam;
use App\Entity\Team;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\CompetitionFixtureTool;
use App\Tool\DateTimeTool;
use App\Tool\UrlTool;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DomCrawler\Crawler;

class CompetitionSeasonMatchCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var array
     */
    private $competitionFixtures = [];

    /**
     * @var Competition
     */
    private $competition;

    /**
     * @var bool
     */
    private $archive = false;

    /**
     * @return Competition
     */
    public function getCompetition(): Competition
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
     * @return CrawlerInterface
     * @throws EntityNotFoundException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $fixtureCollection = $this->getConfigSchema('competition.fixture.collection.url');

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
            $url  = $this->preparePath($fixtureCollection->getUrl(), [$slug, $code, $year]);
            $this->processPath($url);
            $this->competitionFixtures = $this->processFixtureHtml($competitionSeason);
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();
        return $this;
    }

    /**
     *
     */
    public function getData(): array
    {
        return $this->competitionFixtures;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        $em = $this
            ->getDoctrine()
            ->getManager();
        $this->createProgressBar('Saving fixture information', count($this->competitionFixtures));
        foreach ($this->competitionFixtures as $fixture) {
            $em->persist($fixture);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->advanceProgressBar();
        $this->finishProgressBar();
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
    private function processFixtureHtml(CompetitionSeason $competitionSeason)
    {
        $globalUrl = $this->getConfigSchema('global.url');
        $tablesNode = CompetitionFixtureTool::getTableNodes($this->getCrawler());

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

                $fixture = $this->findFixtureByTmkCode($tmkCode);
                if (!$fixture instanceof CompetitionSeasonMatch) {
                    $fixture = new CompetitionSeasonMatch();
                    $fixture->setTmkCode($tmkCode);
                }
                $fixture->setCompetitionSeason($competitionSeason);
                $fixture->setMatchDatetime($matchTime);
                $fixture->setMatchDay($matchDay);
                $schema = (MetadataSchemaResources::createSchema())->setUrl($url);
                $fixture->setMetadata($schema->getSchema());

                //TODO: Add Team for each fixture entry
                $fixture = $this->addCompetitionSeasonMatchTeam($fixture, $homeTmkCode, $cellMatch);
                $fixture = $this->addCompetitionSeasonMatchTeam($fixture, $awayTmkCode, $cellMatch, false);
                $fixtureMatches[] = $fixture;
            }
            $matchDay++;
        }
        return $fixtureMatches;
    }

    private function findFixtureByTmkCode($tmkCode)
    {
        $result = $this->getDoctrine()
            ->getRepository(CompetitionSeasonMatch::class)
            ->findBy(['tmk_code'=>$tmkCode]);
        if (count($result) > 1) {
            $this->getOutput()->writeln("Many fixture matches have been found for $tmkCode code");
        }
        return $this->getDoctrine()
            ->getRepository(CompetitionSeasonMatch::class)
            ->findOneBy(['tmk_code'=>$tmkCode]);
    }

    /**
     * @param CompetitionSeasonMatch $competitionSeasonMatch
     * @param $tmlCode
     * @param $cellMatch
     * @param bool $home
     * @return \Doctrine\Common\Collections\Collection
     * @throws EntityNotFoundException
     */
    private function addCompetitionSeasonMatchTeam(
        CompetitionSeasonMatch $competitionSeasonMatch,
        $tmlCode,
        $cellMatch,
        $home = true): CompetitionSeasonMatch
    {
        $team = $this
            ->getDoctrine()
            ->getRepository(Team::class)
            ->findOneBy(['code' => $tmlCode]);
        if (!$team instanceof Team) {
            throw new EntityNotFoundException("Team with code: $tmlCode not found");
        }

        $result = $competitionSeasonMatch
            ->getCompetitionSeasonMatchTeams()
            ->filter(function (CompetitionSeasonMatchTeam $competitionSFTeam) use($team) {
                return ($competitionSFTeam->getTeam()->getId() === $team->getId());
            });
        $fixtureTeam = null;
        if ($result->count() > 0) {
            $fixtureTeam =$result->first();
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
