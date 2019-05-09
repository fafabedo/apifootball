<?php

namespace App\Service\Processor\Order;

use App\Entity\CompetitionSeasonMatch;
use App\Entity\CompetitionSeasonTable;
use App\Entity\CompetitionSeasonTableItem;
use App\Service\Processor\Table\TableProxy;
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
     * OrderContext constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
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

    public function setMatches($matches): OrderProxy
    {
        $this->getOrderStrategy()->setMatches($matches);
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

    public function process()
    {
        $seasonTables = $this->getCompetitionSeasonTables();
        foreach ($seasonTables as $seasonTable) {
            $tableItems = $seasonTable->getCompetitionSeasonTableItems();
            $arrayTableItems = $tableItems->toArray();
            $arrayTableItems = $this->sortItems($arrayTableItems);

            $seasonTable->getCompetitionSeasonTableItems()->clear();
            /* @var CompetitionSeasonTableItem $item */
            $position = 1;
            foreach ($arrayTableItems as $item) {
                $item->setPosition($position);
                $seasonTable->addCompetitionSeasonTableItem($item);
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


}
