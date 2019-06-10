<?php

namespace App\Service\Metadata;

interface MetadataInterface
{
    static public function createSchema($schema);

    public function setSchema(array $schema): MetadataInterface;

    public function getSchema();

}
