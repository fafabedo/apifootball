<?php

namespace App\Service\Crawler;

interface CrawlerInterface
{
    public function process();

    public function getData();

    public function saveData();
}
