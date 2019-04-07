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

        $configs = $this
            ->doctrine
            ->getRepository(Config::class)
            ->findAll()
        ;

        $metadataSchema = new MetadataSchemaResources();
        $em = $this->doctrine->getManager();

        foreach ($configs as $config) {
            if (!$metadataSchema->validateSchema($config->getData())) {
                if (isset($config->getData()['resource'])) {
                    $url = $config->getData()['resource']['url'];
                    $method = $config->getData()['resource']['method'];
                    $metadataSchema->setUrl($url, $method);
                    $config->setData($metadataSchema->getSchema());
                    $em->persist($config);
                    $io->writeln($config->getName());
                }

            }
        }
        $em->flush();

        $competitions = $this->doctrine
            ->getRepository(Competition::class)
            ->findAll();
        foreach ($competitions as $competition) {
            if (!$metadataSchema->validateSchema($competition->getMetadata())) {
                if (isset($competition->getMetadata()['resource'])) {
                    $url = $competition->getMetadata()['resource']['url'];
                    $method = $competition->getMetadata()['resource']['method'];
                    $metadataSchema->setUrl($url, $method);
                    $competition->setMetadata($metadataSchema->getSchema());
                    $em->persist($competition);
                    $io->writeln($competition->getName());
                }

            }
        }
        $em->flush();

        $competitions = $this->doctrine
            ->getRepository(CompetitionSeason::class)
            ->findAll();
        foreach ($competitions as $competitionSeason) {
            if (empty($competitionSeason->getMetadata())) {
                continue;
            }
            if (!$metadataSchema->validateSchema($competitionSeason->getMetadata())) {
                if (isset($competitionSeason->getMetadata()['resource'])) {
                    $url = $competitionSeason->getMetadata()['resource']['url'];
                    $method = $competitionSeason->getMetadata()['resource']['method'];
                    $metadataSchema->setUrl($url, $method);
                    $competitionSeason->setMetadata($metadataSchema->getSchema());
                    $em->persist($competitionSeason);
                    $io->writeln($competitionSeason->getCompetition()->getName());
                }

            }
        }
        $em->flush();

        $teams = $this->doctrine->getRepository(Team::class)->findAll();
        foreach ($teams as $team) {
            if (empty($team->getMetadata())) {
                continue;
            }
            if (!$metadataSchema->validateSchema($team->getMetadata())) {
                if (isset($team->getMetadata()['resource'])) {
                    $url = $team->getMetadata()['resource']['url'];
                    $method = $team->getMetadata()['resource']['method'];
                    try {
                        $metadataSchema->setUrl($url, $method);
                    }
                    catch (InvalidURLException $e) {
                        $baseUrl = 'https://www.transfermarkt.co.uk';
                        $metadataSchema->setUrl($baseUrl. $url, $method);
                    }
                    $team->setMetadata($metadataSchema->getSchema());
                    $em->persist($team);
                    $io->writeln($team->getName());
                }

            }
        }
        $em->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
