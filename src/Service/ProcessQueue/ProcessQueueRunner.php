<?php

namespace App\Service\ProcessQueue;

use App\Entity\ProcessQueue;
use App\Entity\ProcessQueueLog;
use App\Entity\ProcessQueueOperation;
use App\Exception\InvalidCrawlerProcess;
use App\Exception\ProcessQueue\ProcessQueueNonOngoingOperationFound;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaParameters;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessQueueRunner
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var ProcessQueueManager
     */
    private $processQueueManager;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * ProcessQueueRunner constructor.
     * @param ContainerInterface $container
     * @param ManagerRegistry $doctrine
     * @param ProcessQueueManager $processQueueManager
     */
    public function __construct(
        ContainerInterface $container,
        ManagerRegistry $doctrine,
        ProcessQueueManager $processQueueManager
    ) {
        $this->container = $container;
        $this->doctrine = $doctrine;
        $this->processQueueManager = $processQueueManager;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @return ProcessQueueManager
     */
    public function getProcessQueueManager(): ProcessQueueManager
    {
        return $this->processQueueManager;
    }

    /**
     * @return SymfonyStyle/null
     */
    public function getIo(): ?SymfonyStyle
    {
        return $this->io;
    }

    /**
     * @param SymfonyStyle $io
     * @return ProcessQueueRunner
     */
    public function setIo(SymfonyStyle $io): ProcessQueueRunner
    {
        $this->io = $io;

        return $this;
    }

    /**
     * @return ProcessQueueRunner
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws InvalidCrawlerProcess
     * @throws \Exception
     */
    public function process(): ProcessQueueRunner
    {
        $processQueues = $this
            ->getDoctrine()
            ->getRepository(ProcessQueue::class)
            ->findPendingProcess();
        foreach ($processQueues as $processQueue) {
            $className = $processQueue->getClassName();
            /* @var CrawlerInterface $service */
            $service = $this->loadService($className);
            $parameters = $processQueue->getParameter();
            $metadataParameters = MetadataSchemaParameters::createSchema($parameters);
            foreach ($metadataParameters->getParameters() as $name => $value) {
                $this->setParameter($service, $name, $value);
            }
            $processOperation = $this->getProcessOperation($processQueue);
            if (!$this->isLastExecutionBeforeTimeInterval($processOperation)) {
                continue;
            }
            $this
                ->getProcessQueueManager()
                ->log($processOperation, 'PID: '.$processQueue->getId());
            try {
                $offset = $processOperation->getProcessedItems() + 1;
                $service
                    ->setLimit($processOperation->getBatchLimit())
                    ->setOffset($offset)
                    ->process();
                $service->saveData();
                $data = $service->getData() ?? [];
                $processedItems = count($data);
                $processedBatch = $processOperation->getProcessedItems();
                if (!empty($data)) {
                    $processedBatch += $processOperation->getBatchLimit();
                }
                $timestamp = new \DateTime();
                $message = 'Processed successfully items: '.count($data).' time: '.$timestamp->format('Y-m-d H:i:s');
                $this
                    ->getProcessQueueManager()
                    ->updateStatus($processOperation, $processedItems)
                    ->updateProcessedItems($processOperation, $processedBatch)
                    ->log($processOperation, $message);

                if ($this->getIo() instanceof SymfonyStyle) {
                    $this->getIo()->success('PID ('.$processQueue->getId().') completed');
                }
            } catch (\Exception $e) {
                $this
                    ->getProcessQueueManager()
                    ->log($processOperation, $e->getMessage(), ProcessQueueLog::TYPE_ERROR);
                if ($this->getIo() instanceof SymfonyStyle) {
                    $this->getIo()->error('PID ('.$processQueue->getId().') threw an error');
                }
            }

        }

        return $this;
    }

    /**
     * @param ProcessQueue $processQueue
     * @return ProcessQueueOperation
     * @throws ProcessQueueNonOngoingOperationFound
     */
    private function getProcessOperation(ProcessQueue $processQueue)
    {
        $processQueueOperations = $this
            ->getDoctrine()
            ->getRepository(ProcessQueueOperation::class)
            ->findOneActiveOperation($processQueue);
        if (empty($processQueueOperations)) {
            throw new ProcessQueueNonOngoingOperationFound();
        }

        return $processQueueOperations[0];
    }

    /**
     * @param $className
     * @return CrawlerInterface
     * @throws InvalidCrawlerProcess
     */
    public function loadService($className)
    {
        $this
            ->getContainer()
            ->initialized($className);
        $service = $this
            ->getContainer()
            ->get($className);
        if (!$service instanceof CrawlerInterface) {
            throw new InvalidCrawlerProcess();
        }

        return $service;
    }

    /**
     * @param $service
     * @param $name
     * @param $value
     * @return CrawlerInterface
     */
    private function setParameter($service, $name, $value): CrawlerInterface
    {
        $entityName = Inflector::ucwords(Inflector::camelize($name));
        $method = 'set'.$entityName;
        if (method_exists($service, $method)) {
            if (class_exists('App\\Entity\\'.$entityName)) {
                $value = $this->loadEntity($entityName, $value);
            }
            $service->$method($value);
        }

        return $service;
    }

    /**
     * @param $entityName
     * @param $id
     * @return object|null
     */
    private function loadEntity($entityName, $id)
    {
        $loader = '\\App\\Entity\\'.$entityName;
        $repository = $this->getDoctrine()->getRepository($loader);

        return $repository->find($id);
    }

    /**
     * @param ProcessQueueOperation $processQueueOperation
     * @return bool
     * @throws \Exception
     */
    private function isLastExecutionBeforeTimeInterval(ProcessQueueOperation $processQueueOperation): bool
    {
        if ($processQueueOperation->getStatus() === ProcessQueueOperation::STATUS_ONGOING) {
            return true;
        }
        $processQueue = $processQueueOperation->getProcessQueue();
        $lastExecution = $processQueueOperation->getUpdated();
        $interval = $processQueue->getFrequency();
        $now = new \DateTime();

        return ($processQueue->getType() === ProcessQueue::TYPE_RECURRING
            && ($lastExecution->getTimestamp() + $interval) < $now->getTimestamp());
    }
}
