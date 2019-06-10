<?php

namespace App\Command;

use App\Service\ProcessQueue\ProcessQueueRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FtAppQueueRunCommand extends Command
{
    protected static $defaultName = 'ft-app:queue:run';

    /**
     * @var ProcessQueueRunner
     */
    private $processQueueRunner;
    /**
     * FtAppCronRunCommand constructor.
     * @param ProcessQueueRunner $processQueueRunner
     */
    public function __construct(ProcessQueueRunner $processQueueRunner)
    {
        $this->processQueueRunner = $processQueueRunner;
        parent::__construct();
    }

    /**
     * @return ProcessQueueRunner
     */
    public function getProcessQueueRunner(): ProcessQueueRunner
    {
        return $this->processQueueRunner;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_OPTIONAL, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $this->getProcessQueueRunner()
            ->process();

        $io->success('Processes have been executed successfully');
    }
}
