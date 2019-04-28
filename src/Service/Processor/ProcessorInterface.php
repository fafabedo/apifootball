<?php

namespace App\Service\Processor;

use Symfony\Component\Console\Style\StyleInterface;

interface ProcessorInterface
{
    public function getOutputStyle();

    public function setOutputStyle(StyleInterface $outputStyle);

    public function process();

    public function getData();

    public function saveData();
}
