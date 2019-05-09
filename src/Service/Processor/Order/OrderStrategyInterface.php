<?php

namespace App\Service\Processor\Order;

interface OrderStrategyInterface
{
    public function setMatches($matches);

    public function getMatches();

    public function sortItems($tableItem1, $tableItem2);
}
