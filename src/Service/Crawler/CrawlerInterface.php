<?php

namespace App\Service\Crawler;

interface CrawlerInterface
{
    public function setLimit($limit): CrawlerInterface;

    public function getLimit();

    public function setOffset($offset): CrawlerInterface;

    public function getOffset();

    public function process();

    public function getData();

    public function saveData();

    public function isCompleted(): bool ;
}
