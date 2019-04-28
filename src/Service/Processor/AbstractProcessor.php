<?php

namespace App\Service\Processor;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Style\StyleInterface;

abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var StyleInterface
     */
    private $outputStyle;

    /**
     * TableProcessor constructor.
     * @param ManagerRegistry $doctrine
     * @throws \Exception
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
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
     * @return ProcessorInterface
     */
    public function setOutputStyle(StyleInterface $outputStyle): AbstractProcessor
    {
        $this->outputStyle = $outputStyle;
        return $this;
    }

    abstract public function process();

    abstract public function getData();

    abstract public function saveData();

}
