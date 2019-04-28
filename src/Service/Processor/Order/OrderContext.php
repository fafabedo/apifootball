<?php

namespace App\Service\Processor\Order;

/**
 * Class OrderContext
 * @package App\Service\Processor\Order
 */
class OrderContext
{
    /**
     * @var OrderStrategyInterface
     */
    private $orderStrategy;

    /**
     * OrderContext constructor.
     */
    public function __construct()
    {
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
     * @return OrderContext
     */
    public function setOrderStrategy(OrderStrategyInterface $orderStrategy)
    {
        $this->orderStrategy = $orderStrategy;
        return $this;
    }

    /**
     * @param array $tableItems
     * @return array
     */
    public function sort(array $tableItems)
    {
        usort($tableItems, [$this->getOrderStrategy(), "sortItems"]);
        return $tableItems;
    }


}
