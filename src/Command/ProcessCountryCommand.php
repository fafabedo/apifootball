<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\Entity\Country\CountryCrawler;

class ProcessCountryCommand extends Command
{
    protected static $defaultName = 'app:crw-country';

    /**
     * @var CountryCrawler
     */
    private $countryCrawler;

    /**
     * ProcessCountryCommand constructor.
     * @param CountryCrawler $countryCrawler
     */
    public function __construct(CountryCrawler $countryCrawler)
    {
        $this->countryCrawler = $countryCrawler;
        parent::__construct();
    }

    /**
     * @return CountryCrawler
     */
    public function getCountryCrawler(): CountryCrawler
    {
        return $this->countryCrawler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Process content from Country (transfermkt)')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
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

        $this
            ->getCountryCrawler()
            ->setOutput($output)
            ->process()
            ->saveData()
        ;

        $io->success('Countries have been imported successfully.');
    }
}
