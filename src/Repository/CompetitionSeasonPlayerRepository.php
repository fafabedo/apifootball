<?php

namespace App\Repository;

use App\Entity\CompetitionSeasonPlayer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeasonPlayer|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeasonPlayer|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeasonPlayer[]    findAll()
 * @method CompetitionSeasonPlayer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonPlayerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeasonPlayer::class);
    }

    // /**
    //  * @return CompetitionSeasonPlayer[] Returns an array of CompetitionSeasonPlayer objects
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
    public function findOneBySomeField($value): ?CompetitionSeasonPlayer
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
