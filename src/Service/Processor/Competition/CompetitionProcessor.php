<?php

namespace App\Service\Processor\Competition;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonTable;
use App\Entity\CompetitionType;
use App\Service\Processor\AbstractProcessor;
use App\Service\Processor\Order\OrderProxy;
use App\Service\Processor\Table\TableProxy;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * Class CompetitionProcessor
 * @package App\Service\Processor\Competition
 */
class CompetitionProcessor extends AbstractProcessor
{
    /**
     * @var Competition[]
     */
    private $competitions;

    /**
     * @var CompetitionSeasonTable[]
     */
    private $tables = [];

    /**
     * @var array
     */
    private $matchDay = [];

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var StyleInterface
     */
    private $outputStyle;

    /**
     * @var OrderProxy
     */
    private $orderProxy;

    /**
     * @var TableProxy
     */
    private $tableProxy;

    /**
     * TableProcessor constructor.
     * @param ManagerRegistry $doctrine
     * @param TableProxy $tableProxy
     * @param OrderProxy $orderProxy
     * @throws \Exception
     */
    public function __construct(ManagerRegistry $doctrine, TableProxy $tableProxy, OrderProxy $orderProxy)
    {
        $this->doctrine = $doctrine;
        $this->tableProxy = $tableProxy;
        $this->orderProxy = $orderProxy;
        parent::__construct($doctrine);
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @return Competition[]
     */
    public function getCompetitions(): array
    {
        return $this->competitions;
    }

    /**
     * @param Competition[] $competitions
     * @return CompetitionProcessor
     */
    public function setCompetitions(array $competitions): CompetitionProcessor
    {
        $this->competitions = $competitions;
        return $this;
    }

    /**
     * @return array
     */
    public function getMatchDay(): array
    {
        return $this->matchDay;
    }

    /**
     * @param array $matchDay
     * @return CompetitionProcessor
     */
    public function setMatchDay(array $matchDay): CompetitionProcessor
    {
        $this->matchDay = $matchDay;
        return $this;
    }

    /**
     * @return StyleInterface
     */
    public function getOutputStyle(): StyleInterface
    {
        return $this->outputStyle;
    }

    /**
     * @param StyleInterface $outputStyle
     * @return CompetitionProcessor
     */
    public function setOutputStyle(StyleInterface $outputStyle): CompetitionProcessor
    {
        $this->outputStyle = $outputStyle;
        return $this;
    }

    /**
     * @return TableProxy
     */
    public function getTableProxy(): TableProxy
    {
        return $this->tableProxy;
    }

    /**
     * @return OrderProxy
     */
    public function getOrderProxy(): OrderProxy
    {
        return $this->orderProxy;
    }

    /**
     * @return CompetitionProcessor
     * @throws \Exception
     */
    public function process(): CompetitionProcessor
    {
        $seasons = $this->getCompetitionSeasons();
        foreach ($seasons as $competitionSeason) {
            $competitionTypeId = $competitionSeason
                ->getCompetition()
                ->getCompetitionType()
                ->getId();
            switch ($competitionTypeId) {
                case CompetitionType::TOURNAMENT:
                    $this
                        ->getTableProxy()
                        ->setProcessorByClass('TableGroupsProcessor');
                    ;
                    break;
                case CompetitionType::LEAGUE:
                default:
                    $this
                        ->getTableProxy()
                        ->setProcessorByClass('TableLeagueProcessor')
                        ->setParameter('match_days', $this->getMatchDay());
                    break;
            }
            $tables = $this
                ->getTableProxy()
                ->setCompetitionSeason($competitionSeason)
                ->process()
                ->getData();

            /* @ Order Table position */
            $tables = $this
                ->getOrderProxy()
                ->setStrategyByClass('OrderPointsThenDirectMatches')
                ->setCompetitionSeasonTables($tables)
                ->process()
                ->getData()
            ;

            $this->tables = array_merge($this->tables, $tables);
        }
        return $this;
    }

    /**
     * @return CompetitionSeasonTable[]
     */
    public function getData()
    {
        return $this->tables;
    }

    /**
     * @return CompetitionProcessor
     */
    public function saveData(): CompetitionProcessor
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($this->tables as $table) {
            $em->persist($table);
        }
        $em->flush();
        return $this;
    }

    /**
     * @return CompetitionSeason[]|object[]
     */
    private function getCompetitionSeasons()
    {
        switch (true) {
            case (!empty($this->getCompetitions())):
                return $this
                    ->getDoctrine()
                    ->getRepository(CompetitionSeason::class)
                    ->findBy(['competition' => $this->getCompetitions(), 'archive' => false]);
                break;
            default:
                return $this
                    ->getDoctrine()
                    ->getRepository(CompetitionSeason::class)
                    ->findBy(['archive' => false]);
                break;
        }
    }

}
