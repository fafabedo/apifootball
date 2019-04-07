<?php

namespace App\Traits;

use Symfony\Component\Serializer\Annotation\Groups;

trait MetadataTrait
{
    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $metadata;

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     * @return MetadataTrait
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }
}
