<?php


namespace App\Service\Crawler\Entity\CompetitionSeason;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonTeam;
use App\Entity\Country;
use App\Entity\Team;
use App\Exception\InvalidMetadataSchema;
use App\Exception\InvalidMethodException;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaResources;
use App\Tool\TransferMkt\CompetitionMainPageTool;
use App\Tool\TransferMkt\CompetitionTournamentOverviewTool;
use App\Tool\UrlTool;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CompetitionSeasonCrawler
 * @package App\Service\Entity\CompetitionSeason
 */
class CompetitionSeasonCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var CompetitionSeason[]
     */
    private $competitionSeasons = [];

    /**
     * @var Country
     */
    private $country;

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
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     * @return CompetitionSeasonCrawler
     */
    public function setCountry(Country $country): CompetitionSeasonCrawler
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return Competition/null
     */
    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     * @return CompetitionSeasonCrawler
     */
    public function setCompetition(Competition $competition): CompetitionSeasonCrawler
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
     * @return CompetitionSeasonCrawler
     */
    public function setFeatured(bool $featured): CompetitionSeasonCrawler
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
     * @param CompetitionSeason $competitionseason
     */
    public function setCompetitionSeason(CompetitionSeason $competitionSeason): void
    {
        $this->competitionSeason = $competitionSeason;
    }

    /**
     * @return CrawlerInterface
     * @throws InvalidMethodException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function process(): CrawlerInterface
    {
        $seasons = $this
            ->getDoctrine()
            ->getRepository(CompetitionSeason::class)
            ->findByConfiguration($this->getCompetition(), $this->getCompetitionSeason(), $this->isFeatured());

        $this->createProgressBar('Processing competition seasons', count($seasons));
        foreach ($seasons as $competitionSeason) {
            $metadata = $competitionSeason->getMetadata();
            if (empty($metadata)) {
                continue;
            }
            try {
                $schema = MetadataSchemaResources::createSchema($metadata);
                $this
                    ->setLifetime($this->getCacheLifetime()->getLifetime(CacheLifetime::CACHE_COMPETITION_SEASON))
                    ->processPath($schema->getUrl());
                $this->getCrawler()->html();
                $competitionType = $competitionSeason
                    ->getCompetition()
                    ->getCompetitionType()
                    ->getId();
                switch ($competitionType) {
                    case 1:
                        $competitionSeason = $this->getTeamsTournament($competitionSeason);
                        break;
                    case 2:
                    default:
                        $competitionSeason = $this->getTeamsLeague($competitionSeason);
                        break;
                }
                $this->competitionSeasons[] = $competitionSeason;
            } catch (InvalidMetadataSchema $e) {
                $this->getOutput()->writeln('Invalid Schema: ');
                $this->getOutput()->writeln($competitionSeason->getId());
                $this->getOutput()->writeln($metadata);
            } catch (\Exception $e) {
                throw new \Exception('Error '. __CLASS__ . ' id: ' . $competitionSeason->getId());
            }
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();

        return $this;
    }

    /**
     * @return CompetitionSeason[]
     */
    public function getData(): array
    {
        return $this->competitionSeasons;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        if (empty($this->competitionSeasons)) {
            return $this;
        }
        $em = $this
            ->getDoctrine()
            ->getManager();
        $this->createProgressBar('Saving competition seasons', count($this->competitionSeasons));
        foreach ($this->competitionSeasons as $competitionSeason) {
            if (!$competitionSeason instanceof CompetitionSeason) {
                continue;
            }
            $competitionSeason = $em->merge($competitionSeason);
            $em->persist($competitionSeason);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return CompetitionSeason|null
     * @throws InvalidMethodException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidURLException
     */
    private function getTeamsLeague(CompetitionSeason $competitionSeason): ?CompetitionSeason
    {
        $teamTable = $this
            ->getCrawler()
            ->filterXPath('//div[@id="yw1"]//table//tbody//tr');
        if ($teamTable->count() == 0) {
            return $competitionSeason;
        }
        $tmkCodes = $this->getIDFromTDHtml($teamTable);
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findByTmkCode($tmkCodes);

        $teams = $this->createTeamFromHtml($teams, $competitionSeason->getCompetition()->getCountry());
        $this->saveTeams($teams);
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findByTmkCode($tmkCodes);
        foreach ($teams as $team) {
            $result = $competitionSeason->getCompetitionSeasonTeams()->filter(
                function (CompetitionSeasonTeam $competitionSeasonTeam) use ($team) {
                    return ($competitionSeasonTeam->getTeam()->getId() == $team->getId());
                })->first();
            if ($result === false) {
                $competitionSeasonTeam = new CompetitionSeasonTeam();
                $competitionSeasonTeam->setCompetitionSeason($competitionSeason);
                $competitionSeasonTeam->setTeam($team);
                $competitionSeason->addCompetitionSeasonTeam($competitionSeasonTeam);
            }
        }
        return $competitionSeason;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return CompetitionSeason|null
     * @throws InvalidMetadataSchema
     * @throws InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     */
    private function getTeamsTournament(CompetitionSeason $competitionSeason): ?CompetitionSeason
    {
        $teamSpans = $this
            ->getCrawler()
            ->filter('span.vereinsname');
        if ($teamSpans->count() === 0) {
            return $competitionSeason;
        }
        $tmkCodes = [];
        foreach ($teamSpans as $span) {
            $node = new Crawler();
            $node->addNode($span);
            $htmlSpan = $node->html();
            $tmkCodes[] = CompetitionTournamentOverviewTool::getSpanTeamTmkCode($htmlSpan);
        }
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findByTmkCode($tmkCodes);

        $teams = $this->createTeamFromHtml($teams);
        $this->saveTeams($teams);
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findByTmkCode($tmkCodes);
        foreach ($teams as $team) {
            $result = $competitionSeason->getCompetitionSeasonTeams()->filter(
                function (CompetitionSeasonTeam $competitionSeasonTeam) use ($team) {
                    return ($competitionSeasonTeam->getTeam()->getId() == $team->getId());
                })->first();
            if (!$result instanceof CompetitionSeasonTeam) {
                $competitionSeasonTeam = new CompetitionSeasonTeam();
                $competitionSeasonTeam->setCompetitionSeason($competitionSeason);
                $competitionSeasonTeam->setTeam($team);
                $competitionSeason->addCompetitionSeasonTeam($competitionSeasonTeam);
            }
        }
        return $competitionSeason;
    }

    /**
     * @param Crawler $node
     * @return array
     */
    private function getIDFromTDHtml(Crawler $node)
    {
        $ids = $node->each(function (Crawler $nodeChild, $i) {
            $html = $nodeChild->html();
            preg_match('/id="([0-9]*)"/', $html, $matches);
            $id = null;
            if (isset($matches[1])) {
                $id = $matches[1];
            }
            return $id;
        });


        return $ids;
    }

    /**
     * @param Team[] $existentTeams
     * @param Country $country
     * @return array
     * @throws InvalidMethodException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidURLException
     */
    private function createTeamFromHtml($existentTeams, Country $country = null)
    {
        $baseUrl = $this->getConfigSchema('crawler.global.url');
        $teams = CompetitionMainPageTool::getTeamsFromPage($this->getCrawler());
        $newTeams = [];
        foreach ($teams as $teamInfo) {
            $url = $baseUrl->getUrl() . $teamInfo['url'];
            $tmkCode = UrlTool::getParamFromUrl($url, 4);
            $slug = UrlTool::getParamFromUrl($url, 1);
            $team = $this->getDoctrine()
                ->getRepository(Team::class)
                ->findOneByTmkCode($tmkCode);
            if (!$team instanceof Team){
                $team = new Team();
                $team->setCode($tmkCode);
            }
            $team->setShortname($teamInfo['shortname']);
            $team->setName($teamInfo['name']);
            $team->setSlug($slug);
            if ($country instanceof Country) {
                $team->setCountry($country);
            }
            $schema = MetadataSchemaResources::createSchema();
            $schema->setUrl($url);
            $team->setMetadata($schema->getSchema());
            $newTeams[] = $team;
        }
        $existentTeams = array_merge($existentTeams, $newTeams);
        return $existentTeams;
    }

    /**
     * @param Team[] $teams
     * @return CompetitionSeasonCrawler
     */
    private function saveTeams($teams): CompetitionSeasonCrawler
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($teams as $team) {
            $team = $em->merge($team);
            $em->persist($team);
        }
        $em->flush();
        return $this;
    }

}
