<?php

namespace App\Command;

use App\Entity\Competition;
use App\Entity\Country;
use App\Service\Crawler\Entity\CompetitionSeason\CompetitionSeasonCrawler;
use App\Service\Crawler\Entity\CompetitionSeason\CompetitionSeasonMatchCrawler;
use App\Service\Crawler\Entity\CompetitionSeason\CompetitionSeasonTableCrawler;
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
     * @var CompetitionSeasonTableCrawler
     */
    private $competitionSeasonTableCrawler;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param CompetitionSeasonCrawler $competitionSeasonCrawler
     * @param CompetitionSeasonMatchCrawler $competitionSeasonMatchCrawler
     * @param CompetitionSeasonTableCrawler $competitionSeasonTableCrawler
     */
    public function __construct(ManagerRegistry $doctrine,
        CompetitionSeasonCrawler $competitionSeasonCrawler,
        CompetitionSeasonMatchCrawler $competitionSeasonMatchCrawler,
        CompetitionSeasonTableCrawler $competitionSeasonTableCrawler)
    {
        $this->doctrine = $doctrine;
        $this->competitionSeasonCrawler = $competitionSeasonCrawler;
        $this->competitionSeasonMatchCrawler = $competitionSeasonMatchCrawler;
        $this->competitionSeasonTableCrawler = $competitionSeasonTableCrawler;
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
     * @return CompetitionSeasonTableCrawler
     */
    public function getCompetitionSeasonTableCrawler(): CompetitionSeasonTableCrawler
    {
        return $this->competitionSeasonTableCrawler;
    }

    /**
     * Command configuration
     */
    protected function configure()
    {
        $this
            ->setDescription('Crawl competition seasons, teams and fixture')
            ->addOption('force', null, InputOption::VALUE_OPTIONAL, 'Force match updates', false)
            ->addOption('country', null, InputOption::VALUE_REQUIRED, 'Filter by country')
            ->addOption('competition', null, InputOption::VALUE_REQUIRED, 'Filter by competition')
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
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this
            ->getCompetitionSeasonCrawler()
            ->setOutput($output)
        ;
        $this
            ->getCompetitionSeasonMatchCrawler()
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

        $archive = $input->hasOption('archive');
        $force = $input->hasOption('force');

        $io->title('Competition Seasons...');
        $this
            ->getCompetitionSeasonCrawler()
            ->setShowArchive($archive)
            ->process()
            ->saveData()
        ;
        $competitionSeasons = $this
            ->getCompetitionSeasonCrawler()
            ->getData();
        $io->newLine();
        $io->title('Competition Fixture...');
        $this
            ->getCompetitionSeasonMatchCrawler()
            ->setSeasons($competitionSeasons)
            ->setForceUpdate($force)
            ->process()
            ->saveData()
        ;
        $io->newLine();
        $io->success('Competition matches have been updated successfully.');
    }
}
