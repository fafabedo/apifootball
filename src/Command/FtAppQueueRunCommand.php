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
            ->setDescription('Execute pending process in Queue')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \App\Exception\InvalidCrawlerProcess
     * @throws \App\Exception\InvalidMetadataSchema
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->getProcessQueueRunner()
            ->setIo($io)
            ->process();

        $io->success('Processes have been executed successfully');
    }
}
