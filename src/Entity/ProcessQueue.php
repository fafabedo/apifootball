<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\ProcessQueueRepository")
 */
class ProcessQueue
{
    public const PROCESS_QUEUE_PENDING = 'pending';
    public const PROCESS_QUEUE_PROCESSED = 'processed';
    public const PROCESS_QUEUE_SCHEDULED = 'scheduled';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $className;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $parameter = [];

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processed;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProcessQueueLog", mappedBy="processQueue", orphanRemoval=true, cascade={"persist"})
     */
    private $processQueueLogs;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $frecuency;

    /**
     * ProcessQueue constructor.
     */
    public function __construct()
    {
        $this->processQueueLogs = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return ProcessQueue
     */
    public function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getParameter(): ?array
    {
        return $this->parameter;
    }

    /**
     * @param array|null $parameter
     * @return ProcessQueue
     */
    public function setParameter(?array $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ProcessQueue
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param \DateTimeInterface|null $created
     * @return ProcessQueue
     */
    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getProcessed(): ?\DateTimeInterface
    {
        return $this->processed;
    }

    /**
     * @param \DateTimeInterface|null $processed
     * @return ProcessQueue
     */
    public function setProcessed(?\DateTimeInterface $processed): self
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * @return Collection|ProcessQueueLog[]
     */
    public function getProcessQueueLogs(): Collection
    {
        return $this->processQueueLogs;
    }

    /**
     * @param ProcessQueueLog $processQueueLog
     * @return ProcessQueue
     */
    public function addProcessQueueLog(ProcessQueueLog $processQueueLog): self
    {
        if (!$this->processQueueLogs->contains($processQueueLog)) {
            $this->processQueueLogs[] = $processQueueLog;
            $processQueueLog->setProcessQueue($this);
        }

        return $this;
    }

    /**
     * @param ProcessQueueLog $processQueueLog
     * @return ProcessQueue
     */
    public function removeProcessQueueLog(ProcessQueueLog $processQueueLog): self
    {
        if ($this->processQueueLogs->contains($processQueueLog)) {
            $this->processQueueLogs->removeElement($processQueueLog);
            // set the owning side to null (unless already changed)
            if ($processQueueLog->getProcessQueue() === $this) {
                $processQueueLog->setProcessQueue(null);
            }
        }

        return $this;
    }

    public function getFrecuency(): ?int
    {
        return $this->frecuency;
    }

    public function setFrecuency(?int $frecuency): self
    {
        $this->frecuency = $frecuency;

        return $this;
    }

}
