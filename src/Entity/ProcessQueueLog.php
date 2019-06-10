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
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcessQueue", inversedBy="processQueueLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $processQueue;

    /**
     * @ORM\Column(type="array")
     */
    private $data = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProcessQueue(): ?ProcessQueue
    {
        return $this->processQueue;
    }

    public function setProcessQueue(?ProcessQueue $processQueue): self
    {
        $this->processQueue = $processQueue;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
