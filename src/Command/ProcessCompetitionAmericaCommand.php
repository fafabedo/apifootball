<?php

namespace App\Command;

use App\Service\Crawler\Entity\Competition\CompetitionAmericaCrawler;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessCompetitionAmericaCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:america';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionAmericaCrawler
     */
    private $competitionAmericaCrawler;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param CompetitionAmericaCrawler $competitionFifaCrawler
     */
    public function __construct(ManagerRegistry $doctrine, CompetitionAmericaCrawler $competitionAmericaCrawler)
    {
        $this->doctrine = $doctrine;
        $this->competitionAmericaCrawler = $competitionAmericaCrawler;
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
     * @return CompetitionAmericaCrawler
     */
    public function getCompetitionAmericaCrawler(): CompetitionAmericaCrawler
    {
        return $this->competitionAmericaCrawler;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Crawl competitions from America page')
            ->addOption('club', null, InputOption::VALUE_OPTIONAL, 'Crawl club competitions (default: true)', true)
            ->addOption('international', null, InputOption::VALUE_OPTIONAL,
                'Crawl international competitions (default: true)', true);
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('club')) {
            $this
                ->getCompetitionAmericaCrawler()
                ->setClubCrawl($input->getOption('club'))
            ;
        }
        if ($input->getOption('international')) {
            $this
                ->getCompetitionAmericaCrawler()
                ->setClubCrawl($input->getOption('international'))
            ;
        }

        $this
            ->getCompetitionAmericaCrawler()
            ->setOutput($output)
            ->process()
            ->saveData()
        ;

        $io->success('America competitions have been imported successfully.');
    }
}
