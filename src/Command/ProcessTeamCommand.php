<?php

namespace App\Command;

use App\Entity\Competition;
use App\Entity\Country;
use App\Service\Crawler\Entity\Team\TeamCrawler;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessTeamCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:team';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var TeamCrawler
     */
    private $teamCrawler;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param TeamCrawler $teamCrawler
     */
    public function __construct(ManagerRegistry $doctrine, TeamCrawler $teamCrawler)
    {
        $this->doctrine = $doctrine;
        $this->teamCrawler = $teamCrawler;
        parent::__construct();
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @return TeamCrawler
     */
    public function getTeamCrawler(): TeamCrawler
    {
        return $this->teamCrawler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Crawl all teams in all competitions')
            ->addOption('country', null, InputOption::VALUE_OPTIONAL, 'Select country to process')
            ->addOption('level', null, InputOption::VALUE_OPTIONAL, 'Select country to process')
            ->addOption('competition', null, InputOption::VALUE_REQUIRED, 'Select competition');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->getTeamCrawler()
            ->setFeatured(true)
            ->setLimit(20)
            ->setOutput($output)
            ->process()
            ->saveData();

        $io->success('Teams have been imported successfully.');
    }
}
