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
     * Include archive seasons?
     * @var bool
     */
    private $archive = false;

    /**
     * @var int
     */
    private $maxLevel = 1;

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
    public function showArchive(): bool
    {
        return $this->archive;
    }

    /**
     * @param bool $archive
     * @return CompetitionSeasonCrawler
     */
    public function setShowArchive(bool $archive): CompetitionSeasonCrawler
    {
        $this->archive = $archive;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxLevel(): int
    {
        return $this->maxLevel;
    }

    /**
     * @param int $maxLevel
     * @return CompetitionSeasonCrawler
     */
    public function setMaxLevel(int $maxLevel): CompetitionSeasonCrawler
    {
        $this->maxLevel = $maxLevel;
        return $this;
    }

    /**
     * @return CrawlerInterface
     * @throws InvalidMethodException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(): CrawlerInterface
    {
        $seasons = $this->getCompetitionSeasons();
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
                $competitionSeason = $this->getTeams($competitionSeason);
                $this->competitionSeasons[] = $competitionSeason;
            } catch (InvalidMetadataSchema $e) {
                $this->getOutput()->writeln('Invalid Schema: ');
                $this->getOutput()->writeln($competitionSeason->getId());
                $this->getOutput()->writeln($metadata);
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
            $competitionSeason = $em->merge($competitionSeason);
            $em->persist($competitionSeason);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @return
     */
    private function getCompetitionSeasons()
    {
        $filter = ['archive' => $this->showArchive()];
        switch (true) {
            case ($this->getCompetition() instanceof Competition):
                $filter['competition'] = $this->getCompetition();
                break;
            case ($this->getCountry() instanceof Country):
                $levels = [];
                for($i=1; $i<=$this->getMaxLevel();$i++) {
                    $levels[]=$i;
                }
                $competitions = $this
                    ->getDoctrine()
                    ->getRepository(Competition::class)
                    ->findBy(['country' => $this->getCountry(), 'league_level' => $levels]);
                $filter['competition'] = $competitions;
                break;
            default:
                $levels = [];
                for($i=1; $i<=$this->getMaxLevel();$i++) {
                    $levels[]=$i;
                }
                $competitions = $this
                    ->getDoctrine()
                    ->getRepository(Competition::class)
                    ->findBy(['league_level' => $levels]);
                $filter['competition'] = $competitions;
                break;
        }

        return $this
            ->getDoctrine()
            ->getRepository(CompetitionSeason::class)
            ->findBy($filter);
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return CompetitionSeason|null
     * @throws InvalidMethodException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidURLException
     */
    private function getTeams(CompetitionSeason $competitionSeason): ?CompetitionSeason
    {
        $teamTable = $this
            ->getCrawler()
            ->filterXPath('//div[@id="yw1"]//table//tbody//tr');
        if ($teamTable->count() == 0) {
            return null;
        }
        $codes = $this->getIDFromTDHtml($teamTable);
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBy(['code' => $codes]);


        $teams = $this->createTeamFromHtml($teams, $competitionSeason->getCompetition()->getCountry());
        $this->saveTeams($teams);
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBy(['code' => $codes]);
        foreach ($teams as $team) {
            $result = $team->getCompetitionSeasonTeams()->filter(function (CompetitionSeasonTeam $competitionSeasonTeam
            ) use ($team) {
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
     * @param array $existentTeams
     * @param Country $country
     * @return array
     * @throws InvalidMethodException
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidURLException
     */
    private function createTeamFromHtml(array $existentTeams, Country $country)
    {
        $baseUrl = $this->getConfigSchema('global.url');
        $teams = CompetitionMainPageTool::getTeamsFromPage($this->getCrawler());
        $newTeams = [];
        foreach ($teams as $teamInfo) {
            $url = $baseUrl->getUrl() . $teamInfo['url'];
            $code = UrlTool::getParamFromUrl($url, 4);
            $slug = UrlTool::getParamFromUrl($url, 1);
            $team = $this->findTeamByCodeOrSlug($code, $slug);
            if (!$team instanceof Team){
                $team = new Team();
                $team->setCode($code);
            }
            $team->setShortname($teamInfo['shortname']);
            $team->setName($teamInfo['name']);
            $team->setSlug($slug);
            $team->setCountry($country);
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
     * @return CrawlerInterface
     */
    private function saveTeams($teams): self
    {
        foreach ($teams as $team) {
            $em = $this->getDoctrine()->getManager();
            $team = $em->merge($team);
            $em->persist($team);
        }
        $em->flush();
        return $this;
    }

    /**
     * @param $code
     * @param $slug
     * @return Team|object|null
     */
    private function findTeamByCodeOrSlug($code, $slug)
    {
        $team = $this
            ->getDoctrine()
            ->getRepository(Team::class)
            ->findOneBy(['code' => $code, 'slug' => $slug]);
        if ($team instanceof Team) {
            return $team;
        }
        $team = $this
            ->getDoctrine()
            ->getRepository(Team::class)
            ->findOneBy(['code' => $code]);
        if ($team instanceof Team) {
            return $team;
        }
        return null;
    }

}
