<?php

namespace App\Repository;

use App\Entity\CompetitionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionType[]    findAll()
 * @method CompetitionType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionType::class);
    }

    // /**
    //  * @return CompetitionType[] Returns an array of CompetitionType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompetitionType
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
