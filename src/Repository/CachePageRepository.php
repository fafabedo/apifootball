<?php

namespace App\Repository;

use App\Entity\CachePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CachePage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CachePage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CachePage[]    findAll()
 * @method CachePage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CachePageRepository extends ServiceEntityRepository
{
    /**
     * CachePageRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CachePage::class);
    }

    /**
     * @return mixed
     */
    public function findAllCacheExpired()
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT c
            FROM App\Entity\CachePage c
            WHERE date_add(c.created, c.lifetime, \'SECOND\') < CURRENT_DATE()');
        return $query->execute();
    }

    /**
     * @return mixed
     */
    public function deleteAllCacheExpired()
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'DELETE
            FROM App\Entity\CachePage c
            WHERE date_add(c.created, c.lifetime, \'SECOND\') < CURRENT_DATE()');
        return $query->execute();
    }

    // /**
    //  * @return CachePage[] Returns an array of CachePage objects
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
    public function findOneBySomeField($value): ?CachePage
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
