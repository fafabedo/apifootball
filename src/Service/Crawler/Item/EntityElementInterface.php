<?php

namespace App\Service\Crawler\Item;

interface EntityElementInterface
{
    public function getUrl();

    public function setUrl($url);

    public function getName();

    public function setName($name);

}
