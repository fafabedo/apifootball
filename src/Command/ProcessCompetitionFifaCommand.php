<?php

namespace App\Command;

use App\Service\Crawler\Entity\Competition\CompetitionFifaCrawler;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ProcessCompetitionFifaCommand
 * @package App\Command
 */
class ProcessCompetitionFifaCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:fifa';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionFifaCrawler
     */
    private $competitionFifaCrawler;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param CompetitionFifaCrawler $competitionFifaCrawler
     */
    public function __construct(ManagerRegistry $doctrine, CompetitionFifaCrawler $competitionFifaCrawler)
    {
        $this->doctrine = $doctrine;
        $this->competitionFifaCrawler = $competitionFifaCrawler;
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
     * @return CompetitionFifaCrawler
     */
    public function getCompetitionFifaCrawler(): CompetitionFifaCrawler
    {
        return $this->competitionFifaCrawler;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Crawl competitions from International page')
            ->addOption('international', null, InputOption::VALUE_OPTIONAL,
                'Crawl international competitions (default: true)', true);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this
            ->getCompetitionFifaCrawler()
            ->setOutput($output)
            ->process()
            ->saveData()
        ;

        $io->success('Fifa competitions have been imported successfully.');
    }
}
