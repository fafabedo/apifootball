<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use App\Entity\Country;
use App\Traits\TmkEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Competition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competition[]    findAll()
 * @method Competition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionRepository extends ServiceEntityRepository
{
    use TmkEntityRepositoryTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    /**
     * @param array $competitions
     * @return mixed
     */
    public function findByCompetitions(array $competitions)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.competitionSeasons', 'cs')
            ->where('c.id in (:competition)')
            ->setParameter('competition', $competitions)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Country $country
     * @return mixed
     */
    public function findByCountry(Country $country)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.competitionSeasons', 'cs')
            ->where('c.country = :country')
            ->setParameter('country', $country)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findAllCompetitionFeatured()
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.competitionSeasons', 'cs')
            ->where('c.isFeatured = :featured')
            ->setParameter('featured', true)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Country $country
     * @param array $idCompetitions
     * @param bool $featured
     * @return Competition[]|array|mixed
     */
    public function findByConfiguration(
        Country $country = null,
        array $idCompetitions = [],
        $featured = false
    ) {
        switch (true) {
            case ($featured):
                return $this->findAllCompetitionFeatured();
                break;
            case ($country instanceof Country):
                return $this->findByCountry($country);
                break;
            case (!empty($idCompetitions)):
                return $this->findBy(['id' => $idCompetitions]);
                break;
            default:
                return [];
                break;
        }
    }

    // /**
    //  * @return Competition[] Returns an array of Competition objects
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
    public function findOneBySomeField($value): ?Competition
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
