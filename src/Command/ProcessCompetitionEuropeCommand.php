<?php

namespace App\Command;

use App\Service\Crawler\Entity\Competition\CompetitionEuropeCrawler;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessCompetitionEuropeCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:europe';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionEuropeCrawler
     */
    private $competitionEuropeCrawler;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param CompetitionEuropeCrawler $competitionEuropeCrawler
     */
    public function __construct(ManagerRegistry $doctrine, CompetitionEuropeCrawler $competitionEuropeCrawler)
    {
        $this->doctrine = $doctrine;
        $this->competitionEuropeCrawler = $competitionEuropeCrawler;
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
     * @return CompetitionEuropeCrawler
     */
    public function getCompetitionEuropeCrawler(): CompetitionEuropeCrawler
    {
        return $this->competitionEuropeCrawler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Crawl competitions from Europe page')
            ->addOption('club', null, InputOption::VALUE_OPTIONAL, 'Crawl club competitions (default: true)', true)
            ->addOption('international', null, InputOption::VALUE_OPTIONAL,
                'Crawl international competitions (default: true)', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('club')) {
            $this
                ->getCompetitionEuropeCrawler()
                ->setClubCrawl($input->getOption('club'))
            ;
        }
        if ($input->getOption('international')) {
            $this
                ->getCompetitionEuropeCrawler()
                ->setClubCrawl($input->getOption('international'))
            ;
        }

        $this
            ->getCompetitionEuropeCrawler()
            ->setOutput($output)
            ->process()
            ->saveData();

        $io->success('European competitions have been imported successfully.');
    }
}
