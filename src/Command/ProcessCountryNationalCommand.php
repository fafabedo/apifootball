<?php

namespace App\Command;

use App\Entity\Country;
use App\Service\Crawler\Entity\Competition\CompetitionNationalCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProcessCountryNationalCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:national';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionNationalCrawler
     */
    private $competitionNationCrawler;

    /**
     * ProcessCountryCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param CompetitionNationalCrawler $competitionNationCrawler
     */
    public function __construct(ManagerRegistry $doctrine, CompetitionNationalCrawler $competitionNationCrawler)
    {
        $this->doctrine = $doctrine;
        $this->competitionNationCrawler = $competitionNationCrawler;
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
     * @return CompetitionNationalCrawler
     */
    public function getCompetitionNationCrawler(): CompetitionNationalCrawler
    {
        return $this->competitionNationCrawler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Crawl nation competitions')
            ->addOption('country', null, InputOption::VALUE_OPTIONAL, 'Filter by country');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if ($input->getOption('country')) {
            $country = $this
                ->getDoctrine()
                ->getRepository(Country::class)
                ->find($input->getOption('country'));
            if ($country instanceof Country) {
                $this
                    ->getCompetitionNationCrawler()
                    ->setCountry($country)
                ;
            }
        }
        $this->getCompetitionNationCrawler()
            ->setOutput($output)
            ->process()
            ->saveData()
        ;

        $io->success('National teams have been imported successfully.');
    }
}
