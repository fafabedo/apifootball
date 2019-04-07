<?php

namespace App\Command;

use App\Entity\Country;
use App\Service\Crawler\Entity\Competition\CompetitionCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProcessCompetitionCommand extends Command
{
    protected static $defaultName = 'ft-app:crw:competition';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionCrawler
     */
    private $competitionCrawler;

    /**
     * ProcessCompetitionCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param CompetitionCrawler $competitionCrawler
     */
    public function __construct(ManagerRegistry $doctrine, CompetitionCrawler $competitionCrawler)
    {
        $this->doctrine = $doctrine;
        $this->competitionCrawler = $competitionCrawler;
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
     * @return CompetitionCrawler
     */
    public function getCompetitionCrawler(): CompetitionCrawler
    {
        return $this->competitionCrawler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Crawl club competitions by country')
            ->addOption('country', null, InputOption::VALUE_OPTIONAL, 'Country ID to update competitions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $country = null;
        if ($input->getOption('country')) {
            $countryId = $input->getOption('country');
            $country = $this->getDoctrine()
                ->getRepository(Country::class)
                ->find($countryId);
        }

        $crawler = $this->getCompetitionCrawler();

        if ($country instanceof Country) {
            $crawler->setCountry($country);
        }
        $crawler->setOutput($output)
            ->process()
            ->saveData()
        ;

        $io->success('Competitions have been imported successfully.');
    }
}
