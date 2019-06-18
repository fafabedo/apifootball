<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\ProcessQueueOperationRepository")
 */
class ProcessQueueOperation
{
    use TimestampableTrait;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ONGOING = 'ongoing';
    public const STATUS_PROCESSED = 'processed';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcessQueue", inversedBy="processQueueOperations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $processQueue;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProcessQueueLog", mappedBy="processQueueOperation", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $processQueueLogs;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $batchLimit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $processedItems;

    /**
     * ProcessQueueOperation constructor.
     */
    public function __construct()
    {
        $this->processQueueLogs = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ProcessQueue|null
     */
    public function getProcessQueue(): ?ProcessQueue
    {
        return $this->processQueue;
    }

    /**
     * @param ProcessQueue|null $processQueue
     * @return ProcessQueueOperation
     */
    public function setProcessQueue(?ProcessQueue $processQueue): self
    {
        $this->processQueue = $processQueue;

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
     * @return ProcessQueueOperation
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

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
     * @return ProcessQueueOperation
     */
    public function addProcessQueueLog(ProcessQueueLog $processQueueLog): self
    {
        if (!$this->processQueueLogs->contains($processQueueLog)) {
            $this->processQueueLogs[] = $processQueueLog;
            $processQueueLog->setProcessQueueOperation($this);
        }

        return $this;
    }

    /**
     * @param ProcessQueueLog $processQueueLog
     * @return ProcessQueueOperation
     */
    public function removeProcessQueueLog(ProcessQueueLog $processQueueLog): self
    {
        if ($this->processQueueLogs->contains($processQueueLog)) {
            $this->processQueueLogs->removeElement($processQueueLog);
            // set the owning side to null (unless already changed)
            if ($processQueueLog->getProcessQueueOperation() === $this) {
                $processQueueLog->setProcessQueueOperation(null);
            }
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBatchLimit(): ?int
    {
        return $this->batchLimit;
    }

    /**
     * @param int|null $batchLimit
     * @return ProcessQueueOperation
     */
    public function setBatchLimit(?int $batchLimit): self
    {
        $this->batchLimit = $batchLimit;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getProcessedItems(): ?int
    {
        return $this->processedItems;
    }

    /**
     * @param int|null $processedItems
     * @return ProcessQueueOperation
     */
    public function setProcessedItems(?int $processedItems): self
    {
        $this->processedItems = $processedItems;

        return $this;
    }
}
