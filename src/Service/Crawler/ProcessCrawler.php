<?php


namespace App\Service\Crawler;

use App\Entity\Competition;
use App\Entity\Country;
use App\Service\Entity\Competition\CompetitionCrawler;
use App\Service\Entity\Country\CountryCrawler;
use App\Service\Entity\Team\TeamCrawler;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCrawler
{

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CountryCrawler
     */
    private $countryCrawler;

    /**
     * @var CompetitionCrawler
     */
    private $competitionCrawler;

    /**
     * @var TeamCrawler
     */
    private $teamCrawler;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * ProcessCrawler constructor.
     * @param ManagerRegistry $doctrine
     * @param CountryCrawler $countryCrawler
     * @param CompetitionCrawler $competitionCrawler
     * @param TeamCrawler $teamCrawler
     */
    public function __construct(
        ManagerRegistry $doctrine,
        CountryCrawler $countryCrawler,
        CompetitionCrawler $competitionCrawler,
        TeamCrawler $teamCrawler
    ) {
        $this->doctrine = $doctrine;
        $this->countryCrawler = $countryCrawler;
        $this->competitionCrawler = $competitionCrawler;
        $this->teamCrawler = $teamCrawler;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @return CountryCrawler
     */
    public function getCountryCrawler(): CountryCrawler
    {
        return $this->countryCrawler;
    }

    /**
     * @return CompetitionCrawler
     */
    public function getCompetitionCrawler(): CompetitionCrawler
    {
        return $this->competitionCrawler;
    }

    /**
     * @return TeamCrawler
     */
    public function getTeamCrawler(): TeamCrawler
    {
        return $this->teamCrawler;
    }

    /**
     * @return ProgressBar|null
     */
    public function getProgressBar(): ?ProgressBar
    {
        return $this->progressBar;
    }

    /**
     * @param ProgressBar $progressBar
     * @return $this
     */
    public function setProgressBar(ProgressBar &$progressBar)
    {
        $this->progressBar = $progressBar;
        return $this;
    }

    /**
     * @param null $country_id
     * @param OutputInterface $output
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processCompetition($country_id = null, OutputInterface $output = null)
    {
        $progressBar = null;
        if ($output !== null) {
            $progressBar = new ProgressBar($output, 100);
            $progressBar->start();
        }

        $filter = ['active' => true];
        if ($country_id !== null) {
            $filter['id'] = $country_id;
        }
        $countries = $this->getDoctrine()
            ->getRepository(Country::class)
            ->findBy($filter);

        $step = count($countries)/200;

        foreach ($countries as $country) {
            $params = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'land_id' => $country->getCode(),
                ],
            ];
            $this->getCompetitionCrawler()
                ->processPath('https://www.transfermarkt.co.uk/site/DropDownWettbewerbe', 'POST', $params);
            $content = $this
                ->getCompetitionCrawler()
                ->getContent();
            $competitions = $this
                ->getCompetitionCrawler()
                ->getCompetitionCodes($content);

            if ($output !== null && $progressBar instanceof ProgressBar) {
                $progressBar->advance($step);
            }

            foreach ($competitions as $code => $competition) {
                $this
                    ->getCompetitionCrawler()
                    ->processPath('https://www.transfermarkt.co.uk/jumplist/startseite/wettbewerb/' . $code);
                $this
                    ->getCompetitionCrawler()
                    ->setCountry($country)
                    ->setCode($code)
                    ->process();
                $this
                    ->getCompetitionCrawler()
                    ->saveData();
            }
            if ($output !== null && $progressBar instanceof ProgressBar) {
                $progressBar->advance();
            }

        }
        if ($output !== null && $progressBar instanceof ProgressBar) {
            $progressBar->finish();
        }
        return $this;
    }


    public function processTeam(
        $level = 1,
        $country = null,
        OutputInterface $output = null)
    {
        $progressBar = null;
        if ($output !== null) {
            $progressBar = new ProgressBar($output, 100);
            $progressBar->start();
        }

        $filter = [];
        if ($country !== null) {
            $countryObj = $this->getDoctrine()->getRepository(Country::class)->find($country);
            $filter['country'] = $countryObj;
        }
        $competitions = $this->getDoctrine()
            ->getRepository(Competition::class)
            ->findBy($filter)
        ;

        foreach ($competitions as $competition) {
            if ($competition->getLeagueLevel() > $level) {
                continue;
            }
            $code = $competition->getCode();
            $this->getTeamCrawler()
                ->processPath('https://www.transfermarkt.co.uk/jumplist/startseite/wettbewerb/' . $code);
            $this->getTeamCrawler()
                ->setCompetition($competition)
                ->process();

            $teams = $this->getTeamCrawler()
                ->getData();
            $this->getTeamCrawler()
                ->saveData();

        }
    }
}