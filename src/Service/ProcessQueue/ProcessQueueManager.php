<?php

namespace App\Service\ProcessQueue;

use App\Entity\ProcessQueueLog;
use App\Entity\ProcessQueueOperation;
use App\Exception\ProcessQueue\ProcessQueueNonOngoingOperationFound;
use App\Service\Metadata\MetadataSchemaParameters;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\ProcessQueue;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Process\Process;

class ProcessQueueManager
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * ProcessQueueManager constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @return ObjectManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param $className
     * @param $parameters
     * @param string $type
     * @param int $frequency
     * @param int $batchLimit
     * @return ProcessQueueManager
     * @throws \App\Exception\InvalidMetadataSchema
     */
    public function add(
        $className,
        $parameters,
        $type = ProcessQueue::TYPE_ONCE,
        $frequency = 0,
        $batchLimit = 500
    ) {
        $processQueue = new ProcessQueue();
        $processQueue->setClassName($className);
        $parameterSchema = MetadataSchemaParameters::createSchema();
        foreach ($parameters as $name => $value) {
            $parameterSchema->setParameter($name, $value);
        }
        $processQueue->setParameter($parameterSchema->getSchema());
        $processQueue->setType($type);
        $processQueue->setFrequency($frequency);

        $processQueueOperation = new ProcessQueueOperation();
        $processQueueOperation->setStatus(ProcessQueueOperation::STATUS_PENDING);
        $processQueueOperation->setBatchLimit($batchLimit);
        $processQueue->addProcessQueueOperation($processQueueOperation);

        $this->getEm()->persist($processQueue);
        $this->getEm()->flush();

        return $this;
    }

    /**
     * @param ProcessQueueOperation $processQueueOperation
     * @param string $message
     * @param string $type
     * @return ProcessQueueManager
     */
    public function log(
        ProcessQueueOperation $processQueueOperation,
        $message,
        $type = ProcessQueueLog::TYPE_INFO
    ) {

        $processQueueLog = new ProcessQueueLog();
        $processQueueLog->setData([$message]);
        $processQueueLog->setType($type);
        $processQueueOperation->addProcessQueueLog($processQueueLog);

        $this->getEm()->persist($processQueueOperation);
        $this->getEm()->flush();

        return $this;
    }

    /**
     * @param ProcessQueueOperation $processQueueOperation
     * @param $savedData
     * @return ProcessQueueManager
     */
    public function updateStatus(ProcessQueueOperation $processQueueOperation, $savedData)
    {
        $finished = false;
        if ($savedData === 0) {
            $finished = true;
        }
        $status = $processQueueOperation->getStatus();
        switch (true) {
            case ($status === ProcessQueueOperation::STATUS_ONGOING && $finished):
            case ($status === ProcessQueueOperation::STATUS_PENDING && $finished):
                $status = ProcessQueueOperation::STATUS_PROCESSED;
                break;
            case ($status === ProcessQueueOperation::STATUS_PENDING && !$finished):
                $status = ProcessQueueOperation::STATUS_ONGOING;
                break;
            default:
                return $this;
                break;
        }
        $processQueueOperation->setStatus($status);
        $this->getEm()->persist($processQueueOperation);
        $this->recurringCloneOperation($processQueueOperation);
        $this->getEm()->flush();
        return $this;
    }

    /**
     * @param ProcessQueueOperation $processOperation
     * @return $this
     */
    public function recurringCloneOperation(ProcessQueueOperation $processOperation)
    {
        if ($processOperation->getStatus() !== ProcessQueueOperation::STATUS_PROCESSED) {
            return $this;
        }
        $processQueue = $processOperation->getProcessQueue();
        if ($processQueue->getType() === ProcessQueue::TYPE_RECURRING) {
            $processOperation->setStatus(ProcessQueueOperation::STATUS_PENDING);
            $this->getEm()->persist($processOperation);
        }
        return $this;
    }

    /**
     * @param ProcessQueueOperation $processQueueOperation
     * @param $processedItems
     * @return $this
     */
    public function updateProcessedItems(ProcessQueueOperation $processQueueOperation, $processedItems)
    {
        $processQueueOperation->setProcessedItems($processedItems);
        $this
            ->getEm()
            ->persist($processQueueOperation);
        $this
            ->getEm()
            ->flush();

        return $this;
    }

    /**
     * @param \DateTime $datetime
     * @return $this
     */
    public function clearQueue(\DateTime $datetime)
    {
        $this
            ->getDoctrine()
            ->getRepository(ProcessQueue::class)
            ->deleteProcessedOlderThan($datetime);

        return $this;
    }

    public function pendingProcesses()
    {
        return $this
            ->getDoctrine()
            ->getRepository(ProcessQueue::class)
            ->findPendingProcess();
    }


}
