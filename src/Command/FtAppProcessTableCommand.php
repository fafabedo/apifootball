<?php

namespace App\Command;

use App\Entity\Competition;
use App\Service\Processor\Competition\CompetitionProcessor;
use App\Service\Processor\Table\TableProcessorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FtAppProcessTableCommand extends Command
{
    protected static $defaultName = 'ft-app:process:table';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionProcessor
     */
    private $competitionProcessor;

    /**
     * FtAppProcessTableCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param CompetitionProcessor $competitionProcessor
     */
    public function __construct(ManagerRegistry $doctrine,
        CompetitionProcessor $competitionProcessor)
    {
        $this->doctrine = $doctrine;
        $this->competitionProcessor = $competitionProcessor;
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
     * @return CompetitionProcessor
     */
    public function getCompetitionProcessor(): CompetitionProcessor
    {
        return $this->competitionProcessor;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('option1')) {
            // ...
        }

        $competition = $this
            ->getDoctrine()
            ->getRepository(Competition::class)
            ->find(534);
        $this
            ->getCompetitionProcessor()
            ->setOutputStyle($io)
            ->setCompetitions([$competition])
            ->setMatchDay([35])
            ->process()
            ->saveData()
        ;

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
