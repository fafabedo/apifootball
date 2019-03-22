<?php

namespace App\Repository;

use App\Entity\CompetitionSeasonFixture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeasonFixture|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeasonFixture|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeasonFixture[]    findAll()
 * @method CompetitionSeasonFixture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonFixtureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeasonFixture::class);
    }

    // /**
    //  * @return CompetitionSeasonFixture[] Returns an array of CompetitionSeasonFixture objects
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
    public function findOneBySomeField($value): ?CompetitionSeasonFixture
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
