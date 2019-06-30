<?php

namespace App\Service\Metadata;

use App\Exception\InvalidMetadataSchema;

class MetadataSchemaQueue implements MetadataInterface
{
    public const QUEUE = 'queue';

    private $schema = [
        self::QUEUE => [
        ]
    ];

    /**
     * MetadataSchema constructor.
     * @param $schema
     * @throws InvalidMetadataSchema
     */
    public function __construct($schema = [])
    {
        $this->resetSchema();
        if (!empty($schema)) {
            $this->setSchema($schema);
        }
    }

    /**
     * @param $schema
     * @return MetadataSchemaQueue
     * @throws InvalidMetadataSchema
     */
    static public function createSchema($schema = []): MetadataSchemaQueue
    {
        return new self($schema);
    }

    /**
     * @return array
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * @param array $schema
     * @return MetadataSchemaQueue
     * @throws InvalidMetadataSchema
     */
    public function setSchema(array $schema): MetadataInterface
    {
        if (!$this->validateSchema($schema)) {
            throw new InvalidMetadataSchema();
        }
        $this->schema = $schema;
        return $this;
    }

    /**
     * @param array $schema
     * @return bool
     */
    public function validateSchema(array $schema): bool
    {
        if (!isset($schema[self::QUEUE])) {
            return false;
        }
        return true;
    }

    /**
     * @param $item
     * @return $this
     */
    public function addItem($item)
    {
        $this->schema[self::QUEUE][] = $item;
        return $this;
    }

    /**
     * @param $index
     * @return MetadataSchemaQueue
     */
    public function removeItem($index)
    {
        unset($this->schema[self::QUEUE][$index]);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQueue()
    {
        return $this->schema[self::QUEUE];
    }

    /**
     * @return MetadataSchemaQueue
     */
    public function resetSchema(): self
    {
        $this->schema = [
            self::QUEUE => []
        ];
        return $this;
    }

}
