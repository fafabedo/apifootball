<?php

namespace App\Command;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\Config;
use App\Entity\Team;
use App\Exception\InvalidURLException;
use App\Service\Metadata\MetadataSchemaResources;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('option1', null, InputOption::VALUE_OPTIONAL, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);



        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
