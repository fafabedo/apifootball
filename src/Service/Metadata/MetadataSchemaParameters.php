<?php


namespace App\Service\Metadata;


use App\Exception\InvalidMetadataSchema;

/**
 * Class MetadataSchemaParameters
 * @package App\Service\Metadata
 */
class MetadataSchemaParameters implements MetadataInterface
{
    /**
     *
     */
    public const PARAMETERS = 'parameters';

    /**
     *
     */
    public const MAIN = 'main';

    /**
     * @var array
     */
    private $schema = [
        self::PARAMETERS => [
            self::MAIN => [
            ]
        ]
    ];

    /**
     * MetadataSchemaParameters constructor.
     * @param array $schema
     * @throws InvalidMetadataSchema
     */
    public function __construct(array $schema = null)
    {
        if (empty($schema)) {
            $schema = $this->schema;
        }
        $this->setSchema($schema);
    }

    /**
     * @param array $schema
     * @return MetadataSchemaParameters
     * @throws InvalidMetadataSchema
     */
    static public function createSchema($schema = []): MetadataSchemaParameters
    {
        return new self($schema);
    }

    /**
     * @param array $schema
     * @return MetadataInterface
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
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param $schema
     * @return int
     */
    public function validateSchema($schema)
    {
        return (isset($schema[self::PARAMETERS]) & isset($schema[self::PARAMETERS][self::MAIN]));
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->schema[self::PARAMETERS][self::MAIN];
    }

    /**
     * @param $name
     * @param $value
     */
    public function setParameter($name, $value)
    {
        $this->schema[self::PARAMETERS][self::MAIN][$name] = $value;
    }

    /**
     * @param $name
     * @return |null
     */
    public function getParameter($name)
    {
        if (!isset($this->schema[self::PARAMETERS][self::MAIN][$name])) {
            return null;
        }
        return $this->schema[self::PARAMETERS][self::MAIN][$name];
    }

}
