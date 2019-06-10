<?php

namespace App\Command;

use App\Entity\Competition;
use App\Entity\Team;
use App\Service\Crawler\Entity\Player\PlayerCrawler;
use App\Service\ProcessQueue\ProcessQueueManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FtAppCrwFixConfigCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:fix-config';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var PlayerCrawler
     */
    private $playerCrawler;

    /**
     * @var ProcessQueueManager
     */
    private $processQueueManager;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param PlayerCrawler $playerCrawler
     * @param ProcessQueueManager $processQueueManager
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PlayerCrawler $playerCrawler,
        ProcessQueueManager $processQueueManager
    ) {
        $this->doctrine = $doctrine;
        $this->playerCrawler = $playerCrawler;
        $this->processQueueManager = $processQueueManager;
        parent::__construct();
    }

    /**
     * @return PlayerCrawler
     */
    public function getPlayerCrawler(): PlayerCrawler
    {
        return $this->playerCrawler;
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
            ->setDescription('Add a short description for your command')
            ->addOption('option1', null, InputOption::VALUE_OPTIONAL, 'Option description');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $competition = $this
            ->doctrine
            ->getRepository(Competition::class)
            ->find(1242);

        $team = $this
            ->doctrine
            ->getRepository(Team::class)
            ->find(5038);
//        $this
////            ->getPlayerCrawler()
////            ->setCompetition($competition)
//////            ->setTeam($team)
////            ->setOutput($output)
////            ->process()
////            ->saveData()
////            ;

        $this
            ->getProcessQueueManager()
            ->add('App\\Service\\Crawler\\Entity\\Player\\PlayerCrawler', ['team' => 5038])
            ->add('App\\Service\\Crawler\\Entity\\Player\\PlayerCrawler', ['team' => 5079])
            ->add('App\\Service\\Crawler\\Entity\\Player\\PlayerCrawler', ['team' => 5303])
            ->add('App\\Service\\Crawler\\Entity\\Player\\PlayerCrawler', ['team' => 5309])
            ->add('App\\Service\\Crawler\\Entity\\Player\\PlayerCrawler', ['team' => 8960])
        ;

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
