<?php

namespace App\Service\Crawler\Entity\Match;

use App\Entity\CompetitionSeasonMatch;
use App\Entity\MatchLineup;
use App\Entity\MatchSummary;
use App\Entity\Player;
use App\Entity\Team;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Tool\TransferMkt\MatchSummary\MatchSummaryTool;

class MatchSummaryCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var MatchSummary[]
     */
    private $matchSummaries = [];

    /**
     * @var bool
     */
    private $featured = false;

    /**
     * @var int
     */
    private $competitionId = null;

    /**
     * @var bool
     */
    private $override = false;

    /**
     * @var int
     */
    private $competitionMatchId = null;

    /**
     * @return bool
     */
    public function isFeatured(): bool
    {
        return $this->featured;
    }

    /**
     * @param bool $featured
     * @return MatchSummaryCrawler
     */
    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;
        return $this;
    }

    /**
     * @return int
     */
    public function getCompetitionId(): ?int
    {
        return $this->competitionId;
    }

    /**
     * @param mixed $competitionId
     * @return MatchSummaryCrawler
     */
    public function setCompetitionId($competitionId): self
    {
        $this->competitionId = $competitionId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOverride(): bool
    {
        return $this->override;
    }

    /**
     * @param bool $override
     * @return MatchSummaryCrawler
     */
    public function setOverride(bool $override): self
    {
        $this->override = $override;
        return $this;
    }

    /**
     * @return int
     */
    public function getCompetitionMatchId(): ?int
    {
        return $this->competitionMatchId;
    }

    /**
     * @param int $competitionMatchId
     * @return MatchSummaryCrawler
     */
    public function setCompetitionMatchId(int $competitionMatchId): self
    {
        $this->competitionMatchId = $competitionMatchId;
        return $this;
    }

    /**
     * @return CrawlerInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \App\Exception\InvalidMetadataSchema
     */
    public function process(): CrawlerInterface
    {
        $matchSummarySchema = $this->getConfigSchema('crawler.match_summary.item.url');
        $competitionMatches = $this->getMatchesToCrawl();
        foreach ($competitionMatches as $competitionMatch) {
            $matchSummary = $competitionMatch->getMatchSummary();
            if (!$matchSummary instanceof MatchSummary) {
                $matchSummary = $this->createMatchSummary($competitionMatch);
            }
            $tmkCode = $matchSummary->getTmkCode();
            $preparedUrl = $this->preparePath($matchSummarySchema->getUrl(), [$tmkCode]);
            try {
                $this->processPath($preparedUrl);
                $lineup = MatchSummaryTool::getStartingTeamPositions($this->getCrawler());
                $subs = MatchSummaryTool::getSubs($this->getCrawler());

                $matchSummary = $this->getDoctrine()
                    ->getRepository(MatchSummary::class)
                    ->findOneByCompetitionMatchId($competitionMatch->getId());
                if (!$matchSummary instanceof MatchSummary) {
                    $matchSummary = new MatchSummary();
                    $matchSummary->setCompetitionSeasonMatch($competitionMatch);
                }
                if (!empty($lineup)) {
                    $teams = $competitionMatch->getCompetitionSeasonMatchTeams();
                    foreach ($teams as $cTeam) {
                        $team = $cTeam->getTeam();
                        $index = $cTeam->getIsHome() ? 0 : 1;
                        $matchLineups = $this->processMatchPlayers($team, $lineup[$index], $subs[$index]);
                        foreach ($matchLineups as $matchLineup) {
                            $matchLineup->setMatchSummary($matchSummary);
                            $matchSummary->addMatchLineup($matchLineup);
                        }
                    }
                }
                $this->matchSummaries[] = $matchSummary;

            } catch (\Exception $e) {
                $tmp =1;
            }
        }
        return $this;
    }

    /**
     * @return MatchSummary[]
     */
    public function getData()
    {
        return $this->matchSummaries;
    }

    /**
     * @return MatchSummaryCrawler
     */
    public function saveData(): CrawlerInterface
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($this->matchSummaries as $matchSummary) {
            $em->persist($matchSummary);
        }
        $em->flush();
        return $this;
    }

    /**
     * @param Team $team
     * @param MatchLineupPlayer[] $starters
     * @param MatchLineupPlayer[] $subs
     * @return MatchLineup[]
     */
    private function processMatchPlayers(Team $team, $starters, $subs)
    {
        $playerRepository = $this->getDoctrine()
            ->getRepository(Player::class);
        $matchLineups = [];
        foreach ([$starters, $subs] as $i => $list) {
            $isStarter = $i === 0 ? true: false;
            foreach ($list as $item) {
                $player = $playerRepository->findOneByTmkCode($item->getCode());
                if (!$player instanceof Player) {
                    continue;
                }
                $matchLineup = new MatchLineup();
                $matchLineup->setPlayer($player);
                $matchLineup->setTeam($team);
                $matchLineup->setStarter($isStarter);
                $matchLineup->setLeftPosition($item->getLeft());
                $matchLineup->setTopPosition($item->getTop());
                $matchLineups[] = $matchLineup;
            }
        }
        return $matchLineups;
    }

    /**
     * @param CompetitionSeasonMatch $competitionSeasonMatch
     * @return MatchSummary
     */
    public function createMatchSummary(CompetitionSeasonMatch $competitionSeasonMatch)
    {
        $em = $this->getDoctrine()->getManager();
        $matchSummary = new MatchSummary();
        $matchSummary->setCompetitionSeasonMatch($competitionSeasonMatch);
        $matchSummary->setTmkCode($competitionSeasonMatch->getTmkCode());
        $em->persist($matchSummary);
        $em->flush();
        return $matchSummary;
    }

    /**
     * @return CompetitionSeasonMatch[]|object[]
     */
    public function getMatchesToCrawl()
    {
        switch (true) {
            case $this->isFeatured():
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonMatch::class)
                    ->findByFeaturedCompetition();
                break;
            case ($this->getCompetitionId() !== null):
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonMatch::class)
                    ->findByCompetitionId($this->getCompetitionId());
                break;
            case ($this->getCompetitionMatchId() !== null):
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonMatch::class)
                    ->findByCompetitionMatchId($this->getCompetitionMatchId());
                break;
            default:
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonMatch::class)
                    ->findBy(['id' => 0]);
                break;
        }

    }

}
