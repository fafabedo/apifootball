<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\ProcessQueueLogRepository")
 */
class ProcessQueueLog
{
    public const TYPE_ERROR = 'error';
    public const TYPE_INFO = 'info';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $data = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcessQueueOperation", inversedBy="processQueueLogs")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $processQueueOperation;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $type;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return ProcessQueueLog
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return ProcessQueueOperation|null
     */
    public function getProcessQueueOperation(): ?ProcessQueueOperation
    {
        return $this->processQueueOperation;
    }

    /**
     * @param ProcessQueueOperation|null $processQueueOperation
     * @return ProcessQueueLog
     */
    public function setProcessQueueOperation(?ProcessQueueOperation $processQueueOperation): self
    {
        $this->processQueueOperation = $processQueueOperation;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ProcessQueueLog
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
