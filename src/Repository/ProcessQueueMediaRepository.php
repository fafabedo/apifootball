<?php

namespace App\Repository;

use App\Entity\ProcessQueueMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProcessQueueMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessQueueMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessQueueMedia[]    findAll()
 * @method ProcessQueueMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessQueueMediaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProcessQueueMedia::class);
    }

    /**
     * @param $filename
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByFilename($filename)
    {
        return $this->createQueryBuilder('pqm')
            ->andWhere('pqm.filename like :filename')
            ->setParameter('filename', $filename)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return ProcessQueueMedia[] Returns an array of ProcessQueueMedia objects
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
    public function findOneBySomeField($value): ?ProcessQueueMedia
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
