<?php

namespace App\Service\Processor\Table;

use App\Entity\CompetitionSeason;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TableProxy
 * @package App\Service\Processor\Table
 */
class TableProxy
{
    /**
     * @var TableProcessorInterface
     */
    private $strategyTableProcessor;

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
     * @return TableProxy
     */
    public function setProcessorByClass($className): TableProxy
    {
        $class = 'App\\Service\\Processor\\Table\\Strategy\\' . $className;
        $this->getContainer()->initialized($class);
        $processor = $this->getContainer()->get($class);
        $this->setTableProcessorStrategy($processor);
        return $this;
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
     * @return TableProxy
     */
    public function setTableProcessorStrategy(TableProcessorInterface $strategyTableProcessor): TableProxy
    {
        $this->strategyTableProcessor = $strategyTableProcessor;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return TableProxy
     */
    public function setParameter($name, $value): TableProxy
    {
        $this
            ->getTableProcessorStrategy()
            ->setParameter($name, $value);
        return $this;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return TableProxy
     */
    public function setCompetitionSeason(CompetitionSeason $competitionSeason): TableProxy
    {
        $this
            ->getTableProcessorStrategy()
            ->setCompetitionSeason($competitionSeason)
        ;
        return $this;
    }

    /**
     * @return CompetitionSeason|null
     */
    public function getCompetitionSeason(): ?CompetitionSeason
    {
        return $this
            ->getTableProcessorStrategy()
            ->getCompetitionSeason();
    }

    /**
     * @return $this
     */
    public function process(): TableProxy
    {
        $this
            ->getTableProcessorStrategy()
            ->process();
        return $this;
    }

    /**
     * @return $this
     */
    public function saveData(): TableProxy
    {
        $this
            ->getTableProcessorStrategy()
            ->saveData();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this
            ->getTableProcessorStrategy()
            ->getTable();
    }

}
