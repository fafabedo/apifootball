<?php

namespace App\Repository;

use App\Entity\ProcessQueueLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProcessQueueLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessQueueLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessQueueLog[]    findAll()
 * @method ProcessQueueLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessQueueLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProcessQueueLog::class);
    }

    // /**
    //  * @return ProcessQueueLog[] Returns an array of ProcessQueueLog objects
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
    public function findOneBySomeField($value): ?ProcessQueueLog
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
