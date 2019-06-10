<?php

namespace App\Service\ProcessQueue;

use App\Entity\ProcessQueueLog;
use App\Service\Metadata\MetadataSchemaParameters;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\ProcessQueue;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

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
     * @param string $status
     * @param int $frequency
     * @return ProcessQueueManager
     * @throws \App\Exception\InvalidMetadataSchema
     */
    public function add($className, $parameters, $status = ProcessQueue::PROCESS_QUEUE_PENDING, $frequency = 0)
    {
        $processQueue = new ProcessQueue();
        $processQueue->setClassName($className);
        $parameterSchema = MetadataSchemaParameters::createSchema();
        foreach ($parameters as $name => $value) {
            $parameterSchema->setParameter($name, $value);
        }
        $processQueue->setParameter($parameterSchema->getSchema());
        $processQueue->setStatus($status);
        $processQueue->setFrecuency($frequency);
        $processQueue->setCreated(new \DateTime());
        $this->getEm()->persist($processQueue);
        $this->getEm()->flush();
        return $this;
    }

    public function logData(ProcessQueue $processQueue, $data)
    {
        $processQueueLog = new ProcessQueueLog();
        $processQueueLog->setData($data);
        $processQueue->addProcessQueueLog($processQueueLog);

        $this->getEm()->persist($processQueue);
        $this->getEm()->flush();
        return $processQueue;
    }

    /**
     * @param ProcessQueue $processQueue
     * @param string $status
     * @return $this
     * @throws \Exception
     */
    public function updateStatus(ProcessQueue $processQueue, $status = ProcessQueue::PROCESS_QUEUE_PENDING)
    {
        $processQueue->setStatus($status);
        switch ($status) {
            case ProcessQueue::PROCESS_QUEUE_PROCESSED:
                $date = new \DateTime();
                $processQueue->setProcessed($date);
                break;
            default:
                break;
        }
        $this
            ->getEm()
            ->persist($processQueue);
        $this->getEm()->flush();
        return $this;
    }

    public function clearQueue(\DateTime $datetime)
    {
        $this
            ->getDoctrine()
            ->getRepository(ProcessQueue::class)
            ->deleteProcessedOlderThan($datetime);
        return $this;
    }


}
