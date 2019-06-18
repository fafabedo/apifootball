<?php

namespace App\Command;

use App\Service\ProcessQueue\ProcessQueueManager;
use App\Service\ProcessQueue\ProcessQueueRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FtAppQueueClearCommand extends Command
{
    protected static $defaultName = 'ft-app:queue:clear';

    /**
     * @var ProcessQueueRunner
     */
    private $processQueueRunner;

    /**
     * @var ProcessQueueManager
     */
    private $processQueueManager;

    /**
     * FtAppCronRunCommand constructor.
     * @param ProcessQueueRunner $processQueueRunner
     * @param ProcessQueueManager $processQueueManager
     */
    public function __construct(
        ProcessQueueRunner $processQueueRunner,
        ProcessQueueManager $processQueueManager
    ) {
        $this->processQueueRunner = $processQueueRunner;
        $this->processQueueManager = $processQueueManager;
        parent::__construct();
    }

    /**
     * @return ProcessQueueManager
     */
    public function getProcessQueueManager(): ProcessQueueManager
    {
        return $this->processQueueManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Clear old processed from Queue (by default 2 week)')
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Set specific interval to time', '-2 weeks')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $interval = $input->getOption('interval');
        $intervalTimestamp = strtotime($interval);
        $datetime = new \DateTime();
        $datetime->setTimestamp($intervalTimestamp);
        $this->getProcessQueueManager()
            ->clearQueue($datetime);

        $io->success('Items processed with ' . $interval . ' or before have been cleared');
    }
}
