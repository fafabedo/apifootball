<?php


namespace App\Service\Metadata;


use App\Exception\InvalidMetadataSchema;
use App\Exception\InvalidMethodException;
use App\Exception\InvalidURLException;

class MetadataSchemaResources
{
    public const RESOURCES = 'resources';
    public const URL = 'url';
    public const METHOD = 'method';

    public const MAIN = 'main';

    private $schema = [
        self::RESOURCES => [
            self::MAIN => [
                self::URL => null,
                self::METHOD => 'GET',
            ]
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
     * @return MetadataSchemaResources
     * @throws InvalidMetadataSchema
     */
    static public function createSchema($schema = []): MetadataSchemaResources
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
     * @return MetadataSchemaResources
     * @throws InvalidMetadataSchema
     */
    public function setSchema(array $schema): MetadataSchemaResources
    {
        if (!$this->validateSchema($schema)) {
            throw new InvalidMetadataSchema();
        }
        $this->schema = $schema;
        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getUrl($name = self::MAIN): string
    {
        return $this->schema[self::RESOURCES][$name][self::URL];
    }

    /**
     * @param string $name
     * @return string
     */
    public function getMethod($name = self::MAIN): string
    {
        return $this->schema[self::RESOURCES][$name][self::METHOD];
    }

    /**
     * @param $url
     * @param string $method
     * @param string $name
     * @return MetadataSchemaResources
     * @throws InvalidMethodException
     * @throws InvalidURLException
     */
    public function setUrl($url, $method = 'GET', $name = self::MAIN): MetadataSchemaResources
    {
        if (!$this->validateUrl($url)) {
            throw new InvalidURLException();
        }
        if (!$this->validateMethod($method)) {
            throw new InvalidMethodException();
        }
        $this->schema[self::RESOURCES][$name] = [
            self::URL => $url,
            self::METHOD => $method,
        ];

        return $this;
    }

    /**
     * @param $method
     * @return bool
     */
    private function validateMethod($method): bool
    {
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            return true;
        }
        return false;
    }

    /**
     * @param $url
     * @return bool
     */
    private function validateUrl($url)
    {
        if (preg_match('/http[s|]*:\/\//i', $url)) {
            return true;
        }
        return false;
    }

    /**
     * @param array $schema
     * @return bool
     */
    public function validateSchema(array $schema): bool
    {
        if (!isset($schema[self::RESOURCES]) || !isset($schema[self::RESOURCES][self::MAIN])) {
            return false;
        }
        return true;
    }

    /**
     * @return MetadataSchemaResources
     */
    public function resetSchema(): self
    {
        $this->schema = [
            self::RESOURCES => [
                self::MAIN => [
                    self::URL => null,
                    self::METHOD => 'GET'
                ]
            ]
        ];
        return $this;
    }

}
