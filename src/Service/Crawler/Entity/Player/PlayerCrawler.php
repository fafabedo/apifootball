<?php


namespace App\Service\Crawler\Entity\Player;


use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonTeamPlayer;
use App\Entity\Player;
use App\Entity\Team;
use App\Service\Cache\CacheLifetime;
use App\Service\Crawler\ContentCrawler;
use App\Service\Crawler\CrawlerInterface;
use App\Tool\TransferMkt\Player\PlayerProfileTool;
use Cocur\Slugify\Slugify;

class PlayerCrawler extends ContentCrawler implements CrawlerInterface
{
    /**
     * @var Team
     */
    private $team;

    /**
     * @var Competition
     */
    private $competition;

    /**
     * @var CompetitionSeason
     */
    private $competitionSeason;

    /**
     * @var Player[]
     */
    private $players;

    /**
     * @return Team
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team $team
     * @return PlayerCrawler
     */
    public function setTeam(Team $team): PlayerCrawler
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Competition
     */
    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     * @return PlayerCrawler
     */
    public function setCompetition(Competition $competition): PlayerCrawler
    {
        $this->competition = $competition;

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
     * @return PlayerCrawler
     */
    public function setCompetitionSeason(CompetitionSeason $competitionSeason): PlayerCrawler
    {
        $this->competitionSeason = $competitionSeason;

        return $this;
    }

    /**
     * @return PlayerCrawler
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function process(): CrawlerInterface
    {
        $seasonTeamPlayers = $this->getSeasonTeamPlayerList();
        $this->createProgressBar('Processing players ..', count($seasonTeamPlayers));
        $index = 1;
        foreach ($seasonTeamPlayers as $seasonTeamPlayer) {
            if (!$this->validOffset($index)) {
                continue;
            }
            $player = $seasonTeamPlayer->getPlayer();
            $player = $this->getProfileInfo($player);
            $this->players[] = $player;
            $this->advanceProgressBar();
            $index++;
        }
        $this->finishProgressBar();
        return $this;
    }

    public function getData()
    {
        return $this->players;
    }

    /**
     * @return CrawlerInterface
     */
    public function saveData(): CrawlerInterface
    {
        if (empty($this->players)) {
            return $this;
        }
        $this->createProgressBar('Saving players ..', count($this->players));
        $em = $this->getDoctrine()->getManager();
        foreach ($this->players as $player) {
            $em->persist($player);
            $this->advanceProgressBar();
        }
        $em->flush();
        $this->finishProgressBar();
        return $this;
    }

    /**
     * @param Player $player
     * @return Player
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getProfileInfo(Player $player)
    {
        $playerProfileUrl = $this->getConfigSchema('crawler.player.profile.url');
        $slugify = new Slugify();
        $slug = $slugify->slugify($player->getName());
        $tmkCode = $player->getTmkCode();
        $preparedUrl = $this->preparePath($playerProfileUrl->getUrl(), [$slug, $tmkCode]);
        $lifetime = $this
            ->getCacheLifetime()
            ->getLifetime(CacheLifetime::CACHE_PLAYER);
        $this
            ->setLifetime($lifetime)
            ->processPath($preparedUrl)
        ;

        try {
            $this->getCrawler()->html();
            $profileContainer = PlayerProfileTool::getContainerProfile($this->getCrawler());
            $name = PlayerProfileTool::getName($this->getCrawler());
            $jerseyNumber = PlayerProfileTool::getJerseyNumber($this->getCrawler());
            $profilePicture = PlayerProfileTool::getProfilePictureFilename($this->getCrawler());

            $fullName = PlayerProfileTool::getFullName($profileContainer);
            $birthDay = PlayerProfileTool::getBirthday($profileContainer);
            $placeBirth = PlayerProfileTool::getPlaceBirth($profileContainer);
            $height = PlayerProfileTool::getHeight($profileContainer);
            $foot = PlayerProfileTool::getFoot($profileContainer);

            // Update
            $player->setName($name);
            $player->setJerseyNumber($jerseyNumber);
            $player->setFullName($fullName);
            if (isset($birthDay)) {
                $timestamp = strtotime($birthDay);
                $birthDate = new \DateTime();
                $birthDate->setTimestamp($timestamp);
                $player->setBirthday($birthDate);
            }
            $player->setPlaceOfBirth($placeBirth);
            $player->setHeight($height);
            $player->setFoot($foot);
        }
        catch (\Exception $e) {
            throw new \Exception('Player code (' . $player->getTmkCode() . '):' .$e->getMessage());
        }
        return $player;
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    private function getSeasonTeamPlayerList()
    {
        switch (true) {
            case ($this->getCompetitionSeason() instanceof CompetitionSeason):
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonTeamPlayer::class)
                    ->findByCompetitionSeason($this->getCompetitionSeason());
                break;
            case ($this->getCompetition() instanceof Competition):
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonTeamPlayer::class)
                    ->findByCompetition($this->getCompetition());
                break;
            case ($this->getTeam() instanceof Team):
                return $this->getDoctrine()
                    ->getRepository(CompetitionSeasonTeamPlayer::class)
                    ->findByTeam($this->getTeam());
                break;
            default:
                return [];
                break;
        }

    }
}
