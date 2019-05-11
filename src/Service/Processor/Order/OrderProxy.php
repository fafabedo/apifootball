<?php

namespace App\Service\Processor\Order;

use App\Entity\CompetitionSeason;
use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonTable;
use App\Entity\CompetitionSeasonTableItem;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OrderProxy
 * @package App\Service\Processor\Order
 */
class OrderProxy
{
    /**
     * @var CompetitionSeasonTable[]
     */
    private $competitionSeasonTables = [];

    /**
     * @var OrderStrategyInterface
     */
    private $orderStrategy;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var CompetitionSeasonMatch[]
     */
    private $matches = [];

    /**
     * OrderContext constructor.
     * @param ContainerInterface $container
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ContainerInterface $container, ManagerRegistry $doctrine)
    {
        $this->container = $container;
        $this->doctrine = $doctrine;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @param $className
     * @return OrderProxy
     */
    public function setStrategyByClass($className): OrderProxy
    {
        $class = 'App\\Service\\Processor\\Order\\Strategy\\' . $className;
        $this->getContainer()->initialized($class);
        $processor = $this->getContainer()->get($class);
        $this->setOrderStrategy($processor);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderStrategy(): OrderStrategyInterface
    {
        return $this->orderStrategy;
    }

    /**
     * @param mixed $orderStrategy
     * @return OrderProxy
     */
    public function setOrderStrategy(OrderStrategyInterface $orderStrategy): OrderProxy
    {
        $this->orderStrategy = $orderStrategy;
        return $this;
    }

    /**
     * @return CompetitionSeasonTable[]
     */
    public function getCompetitionSeasonTables(): array
    {
        return $this->competitionSeasonTables;
    }

    /**
     * @param CompetitionSeasonTable[] $competitionSeasonTables
     * @return OrderProxy
     */
    public function setCompetitionSeasonTables(array $competitionSeasonTables): OrderProxy
    {
        $this->competitionSeasonTables = $competitionSeasonTables;
        return $this;
    }

    /**
     * @param CompetitionSeasonTableItem[] $tableItems
     * @return array
     */
    public function sortItems(array $tableItems)
    {
        usort($tableItems, [$this->getOrderStrategy(), "sortItems"]);
        return $tableItems;
    }

    /**
     * @return $this
     */
    public function process()
    {
        foreach ($this->competitionSeasonTables as $key => $seasonTable) {
            $tableItems = $this->competitionSeasonTables[$key]
                ->getCompetitionSeasonTableItems();
            $matches = $this->getMatchesBySeason($seasonTable->getCompetitionSeason());
            $this->getOrderStrategy()->setMatches($matches);
            $arrayTableItems = $tableItems->toArray();
            $arrayTableItems = $this->sortItems($arrayTableItems);
            $this->competitionSeasonTables[$key]->getCompetitionSeasonTableItems()->clear();
            /* @var CompetitionSeasonTableItem $item */
            $position = 1;
            foreach ($arrayTableItems as $tableItem) {
                $tableItem->setPosition($position);
                $this->competitionSeasonTables[$key]->addCompetitionSeasonTableItem($tableItem);
                $position++;
            }
        }
        return $this;
    }

    /**
     * @return CompetitionSeasonTable[]|array
     */
    public function getData()
    {
        return $this->getCompetitionSeasonTables();
    }

    private function getMatchesBySeason(CompetitionSeason $competitionSeason)
    {
        $competitionSeasonId = $competitionSeason->getId();
        if (!isset($this->matches[$competitionSeasonId])) {
            $this->matches[$competitionSeasonId] = $this
                ->getDoctrine()
                ->getRepository(CompetitionSeasonMatch::class)
                ->findMatchesBySeason($competitionSeason);
        }
        return $this->matches[$competitionSeasonId];
    }


}
