<?php

namespace App\Repository;

use App\Entity\ProcessQueue;
use App\Entity\ProcessQueueOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProcessQueueOperation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessQueueOperation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessQueueOperation[]    findAll()
 * @method ProcessQueueOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessQueueOperationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProcessQueueOperation::class);
    }

    public function findOneActiveOperation(ProcessQueue $processQueue)
    {
        return $this->createQueryBuilder('pqo')
            ->andWhere('pqo.processQueue = :process_queue')
            ->andWhere('pqo.status in (:status)')
            ->setParameter('process_queue', $processQueue)
            ->setParameter('status', [
                'pending',
                'ongoing'
            ])
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return ProcessQueueOperation[] Returns an array of ProcessQueueOperation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProcessQueueOperation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
