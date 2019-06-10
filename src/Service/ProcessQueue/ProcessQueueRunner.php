<?php

namespace App\Service\ProcessQueue;

use App\Entity\ProcessQueue;
use App\Exception\InvalidCrawlerProcess;
use App\Service\Crawler\CrawlerInterface;
use App\Service\Metadata\MetadataSchemaParameters;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Inflector\Inflector;

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
     * ProcessQueueRunner constructor.
     * @param ContainerInterface $container
     * @param ManagerRegistry $doctrine
     * @param ProcessQueueManager $processQueueManager
     */
    public function __construct(ContainerInterface $container,
        ManagerRegistry $doctrine,
        ProcessQueueManager $processQueueManager)
    {
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
     * @throws \App\Exception\InvalidMetadataSchema
     * @throws InvalidCrawlerProcess
     */
    public function process()
    {
        $processQueues = $this
            ->getDoctrine()
            ->getRepository(ProcessQueue::class)
            ->findPendingProcess();
        foreach ($processQueues as $processQueue) {
            $className = $processQueue->getClassName();
            $service = $this->loadService($className);
            $parameters = $processQueue->getParameter();
            $metadataParameters = MetadataSchemaParameters::createSchema($parameters);
            foreach ($metadataParameters->getParameters() as $name => $value) {
                $this->setParameter($service, $name, $value);
            }
            try {
                $data = $service
                    ->process()
                    ->saveData()
                    ->getData();
                $timestamp = new \DateTime();
                $this
                    ->getProcessQueueManager()
                    ->updateStatus($processQueue,ProcessQueue::PROCESS_QUEUE_PROCESSED)
                    ->logData($processQueue, ['ok' => 'Processed items: '. count($data) . ' time: ' . $timestamp->format('Y-m-d H:i:s')]);
                ;
            }
            catch (\Exception $e) {
                $this
                    ->getProcessQueueManager()
                    ->logData($processQueue, ['error' => $e->getMessage()])
                ;
            }
        }
        return $this;
    }

    /**
     * @param $className
     * @return CrawlerInterface
     * @throws InvalidCrawlerProcess
     */
    public function loadService($className)
    {
//        $class = 'App\\Service\\Processor\\Table\\Strategy\\' . $className;
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
        $loader = '\\App\\Entity\\' . $entityName;
        $repository = $this->getDoctrine()->getRepository($loader);
        return $repository->find($id);
    }
}
