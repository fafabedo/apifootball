<?php

namespace App\Command;

use App\Entity\Country;
use App\Service\Crawler\ProcessCrawler;
use App\Service\Entity\Team\TeamCrawler;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessTeamCommand extends Command
{
    protected static $defaultName = 'app:process-team';

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
            ->setDescription('Add a short description for your command')
            ->addOption('country', null, InputOption::VALUE_OPTIONAL, 'Select country to process')
            ->addOption('level', null, InputOption::VALUE_OPTIONAL, 'Select country to process')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $country = null;
        if ($input->getOption('country')) {
            $countryId = $input->getOption('country');
            $country = $this->getDoctrine()
                ->getRepository(Country::class)
                ->find($countryId);
        }

        $level = 1;
        if ($input->getOption('level')) {
            $level = $input->getOption('level');
        }

        $crawler = $this->getTeamCrawler();

        if ($country instanceof Country) {
            $crawler->setCountry($country);
        }
        $crawler->setLevel($level)
            ->setOutput($output)
            ->process()
            ->saveData()
        ;

        $io->success('Teams have been imported successfully.');
    }
}
