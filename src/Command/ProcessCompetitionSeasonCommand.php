<?php

namespace App\Command;

use App\Entity\Competition;
use App\Entity\Country;
use App\Service\Crawler\Entity\CompetitionSeason\CompetitionSeasonCrawler;
use App\Service\Crawler\Entity\CompetitionSeason\CompetitionSeasonMatchCrawler;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessCompetitionSeasonCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:seasons';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionSeasonCrawler
     */
    private $competitionSeasonCrawler;

    /**
     * @var CompetitionSeasonMatchCrawler
     */
    private $competitionSeasonMatchCrawler;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param CompetitionSeasonCrawler $competitionSeasonCrawler
     * @param CompetitionSeasonMatchCrawler $competitionSeasonMatchCrawler
     */
    public function __construct(ManagerRegistry $doctrine,
        CompetitionSeasonCrawler $competitionSeasonCrawler,
        CompetitionSeasonMatchCrawler $competitionSeasonMatchCrawler)
    {
        $this->doctrine = $doctrine;
        $this->competitionSeasonCrawler = $competitionSeasonCrawler;
        $this->competitionSeasonMatchCrawler = $competitionSeasonMatchCrawler;
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
     * @return CompetitionSeasonCrawler
     */
    public function getCompetitionSeasonCrawler(): CompetitionSeasonCrawler
    {
        return $this->competitionSeasonCrawler;
    }

    /**
     * @return CompetitionSeasonMatchCrawler
     */
    public function getCompetitionSeasonMatchCrawler(): CompetitionSeasonMatchCrawler
    {
        return $this->competitionSeasonMatchCrawler;
    }

    /**
     * Command configuration
     */
    protected function configure()
    {
        $this
            ->setDescription('Crawl competition seasons, teams and fixture')
            ->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'Crawl all competition season with fixture and stats', true)
            ->addOption('country', null, InputOption::VALUE_OPTIONAL, 'Filter by country')
            ->addOption('competition', null, InputOption::VALUE_OPTIONAL, 'Filter by competition')
            ->addOption('archive', null, InputOption::VALUE_OPTIONAL, 'Include past season', false)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \App\Exception\InvalidMethodException
     * @throws \App\Exception\InvalidURLException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this
            ->getCompetitionSeasonCrawler()
            ->setOutput($output)
        ;

        if ($input->getOption('country')) {
            $countryObj = $this
                ->getDoctrine()
                ->getRepository(Country::class)
                ->find($input->getOption('country'))
            ;
            $this
                ->getCompetitionSeasonCrawler()
                ->setCountry($countryObj)
            ;
        }
        if ($input->getOption('competition')) {
            $competitionId = $input->getOption('competition');
            $competition = $this
                ->getDoctrine()
                ->getRepository(Competition::class)
                ->find($competitionId)
            ;
            $this
                ->getCompetitionSeasonCrawler()
                ->setCompetition($competition);
            $this
                ->getCompetitionSeasonMatchCrawler()
                ->setCompetition($competition);

        }

        $archive = $input->getOption('archive');

        $io->title('Competition Seasons');
//        $this
//            ->getCompetitionSeasonCrawler()
//            ->setShowArchive($archive)
//            ->process()
//            ->saveData()
//        ;
        $io->newLine();
        $io->title('Competition Fixture');
        $this
            ->getCompetitionSeasonMatchCrawler()
            ->process()
            ->saveData()
        ;

        $io->success('Competition season has been processed');
    }
}
