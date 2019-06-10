<?php

namespace App\Repository;

use App\Entity\ProcessQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProcessQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessQueue[]    findAll()
 * @method ProcessQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessQueueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProcessQueue::class);
    }

    /**
     * @return ProcessQueue[]
     */
    public function findPendingProcess()
    {
        return $this->createQueryBuilder('pq')
            ->where('pq.status in (:status)')
            ->setParameter('status', [ProcessQueue::PROCESS_QUEUE_PENDING, ProcessQueue::PROCESS_QUEUE_SCHEDULED])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \DateTime $datetime
     * @return mixed
     */
    public function deleteProcessedOlderThan(\DateTime $datetime)
    {
        return $this->createQueryBuilder()
            ->delete('ProcessQueue', 'pq')
            ->where('pq.status = :processed')
            ->andWhere('pq.processed < :datetime')
            ->setParameter('processed', ProcessQueue::PROCESS_QUEUE_PROCESSED)
            ->setParameter('datetime', $datetime)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return ProcessQueue[] Returns an array of ProcessQueue objects
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
    public function findOneBySomeField($value): ?ProcessQueue
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
