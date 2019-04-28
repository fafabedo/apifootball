<?php

namespace App\Service\Processor\Table;

/**
 * Class TableContext
 * @package App\Service\Processor\Table
 */
class TableContext
{
    /**
     * @var TableProcessorInterface
     */
    private $strategyTableProcessor;

    /**
     * OrderContext constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return TableProcessorInterface
     */
    public function getTableProcessorStrategy(): TableProcessorInterface
    {
        return $this->strategyTableProcessor;
    }

    /**
     * @param TableProcessorInterface $strategyTableProcessor
     * @return TableContext
     */
    public function setTableProcessorStrategy(TableProcessorInterface $strategyTableProcessor): TableContext
    {
        $this->strategyTableProcessor = $strategyTableProcessor;
        return $this;
    }

    /**
     * @return $this
     */
    public function process(): TableContext
    {
        $this->getTableProcessorStrategy()->process();
        return $this;
    }

    /**
     * @return $this
     */
    public function saveData(): TableContext
    {
        $this->getTableProcessorStrategy()->saveData();
        return $this;
    }

    /**
     * @return $this
     */
    public function getData(): TableContext
    {
        $this->getTableProcessorStrategy()->getData();
        return $this;
    }

}
