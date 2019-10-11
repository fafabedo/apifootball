<?php

namespace App\Command;

use App\Entity\Competition;
use App\Entity\ProcessQueue;
use App\Entity\Team;
use App\Service\Crawler\Entity\Match\MatchSummaryCrawler;
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
    protected static $defaultName = 'ft-app:crw:run';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var PlayerCrawler
     */
    private $playerCrawler;

    /**
     * @var MatchSummaryCrawler
     */
    private $matchSummaryCrawler;

    /**
     * @var ProcessQueueManager
     */
    private $processQueueManager;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param PlayerCrawler $playerCrawler
     * @param MatchSummaryCrawler $matchSummaryCrawler
     * @param ProcessQueueManager $processQueueManager
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PlayerCrawler $playerCrawler,
        MatchSummaryCrawler $matchSummaryCrawler,
        ProcessQueueManager $processQueueManager
    ) {
        $this->doctrine = $doctrine;
        $this->playerCrawler = $playerCrawler;
        $this->matchSummaryCrawler = $matchSummaryCrawler;
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

        $this->matchSummaryCrawler
            ->setCompetitionMatchId(2067)
            ->process()
            ->saveData();

//        $frequency = 60 * 60 * 24; // 1 day
//        $this
//            ->getProcessQueueManager()
//            ->add('App\\Service\\Crawler\\CachePageClearCrawler',
//                [],
//                ProcessQueue::TYPE_RECURRING,
//                $frequency * 1,
//                100)
//            ->add('App\\Service\\Crawler\\Entity\\CompetitionSeason\\CompetitionSeasonCrawler',
//                ['featured' => true],
//                ProcessQueue::TYPE_RECURRING,
//                $frequency * 30,
//                100)
//            ->add(
//                'App\\Service\\Crawler\\Entity\\CompetitionSeason\\CompetitionSeasonTeamCrawler',
//                ['featured' => true],
//                ProcessQueue::TYPE_RECURRING,
//                $frequency * 30,
//                20
//            )
//            ->add(
//                'App\\Service\\Crawler\\Entity\\CompetitionSeason\\CompetitionSeasonPlayerCrawler',
//                ['featured' => true],
//                ProcessQueue::TYPE_RECURRING,
//                $frequency * 30,
//                20
//            )
//            ->add(
//                'App\\Service\\Crawler\\Entity\\CompetitionSeason\\CompetitionSeasonMatchCrawler',
//                ['featured' => true],
//                ProcessQueue::TYPE_RECURRING,
//                $frequency * 1,
//                20
//            )
//        ;

        $io->success('All tasks have been completed');
    }
}
