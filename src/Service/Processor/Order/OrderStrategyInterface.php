<?php

namespace App\Service\Processor\Order;

interface OrderStrategyInterface
{
    public function sortItems($tableItem1, $tableItem2);
}
