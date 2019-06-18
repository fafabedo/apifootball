<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\ProcessQueueRepository")
 */
class ProcessQueue
{
    use TimestampableTrait;

    public const TYPE_RECURRING = 'recurring';
    public const TYPE_ONCE = 'once';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $className;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $parameter = [];

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $frequency;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProcessQueueOperation", mappedBy="processQueue", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $processQueueOperations;

    /**
     * ProcessQueue constructor.
     */
    public function __construct()
    {
        $this->processQueueOperations = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * @return ProcessQueue
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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
     * @return int|null
     */
    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    /**
     * @param int|null $frequency
     * @return ProcessQueue
     */
    public function setFrequency(?int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return Collection|ProcessQueueOperation[]
     */
    public function getProcessQueueOperations(): Collection
    {
        return $this->processQueueOperations;
    }

    /**
     * @param ProcessQueueOperation $processQueueOperation
     * @return ProcessQueue
     */
    public function addProcessQueueOperation(ProcessQueueOperation $processQueueOperation): self
    {
        if (!$this->processQueueOperations->contains($processQueueOperation)) {
            $this->processQueueOperations[] = $processQueueOperation;
            $processQueueOperation->setProcessQueue($this);
        }

        return $this;
    }

    /**
     * @param ProcessQueueOperation $processQueueOperation
     * @return ProcessQueue
     */
    public function removeProcessQueueOperation(ProcessQueueOperation $processQueueOperation): self
    {
        if ($this->processQueueOperations->contains($processQueueOperation)) {
            $this->processQueueOperations->removeElement($processQueueOperation);
            // set the owning side to null (unless already changed)
            if ($processQueueOperation->getProcessQueue() === $this) {
                $processQueueOperation->setProcessQueue(null);
            }
        }

        return $this;
    }

}
