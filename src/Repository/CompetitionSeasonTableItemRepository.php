<?php

namespace App\Repository;

use App\Entity\CompetitionSeasonTableItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeasonTableItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeasonTableItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeasonTableItem[]    findAll()
 * @method CompetitionSeasonTableItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonTableItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeasonTableItem::class);
    }

    // /**
    //  * @return CompetitionSeasonTableItem[] Returns an array of CompetitionSeasonTableItem objects
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
    public function findOneBySomeField($value): ?CompetitionSeasonTableItem
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
