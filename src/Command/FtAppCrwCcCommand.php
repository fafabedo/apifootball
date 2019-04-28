<?php

namespace App\Command;

use App\Entity\CachePage;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FtAppCrwCcCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:cc';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        parent::__construct();
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    protected function configure()
    {
        $this
            ->setDescription('Clear expired cache entries')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);


        if ($input->getOption('option1')) {
            // ...
        }

        $toDeleted = $this->getDoctrine()->getRepository(CachePage::class)
            ->findAllCacheExpired();
        $this
            ->getDoctrine()
            ->getRepository(CachePage::class)
            ->deleteAllCacheExpired()
        ;

        $io->success('(' . count($toDeleted) . ') entries have been removed');
    }
}
