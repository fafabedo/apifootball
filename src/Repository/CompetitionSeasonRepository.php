<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\CompetitionSeason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompetitionSeason|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionSeason|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionSeason[]    findAll()
 * @method CompetitionSeason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionSeasonRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompetitionSeason::class);
    }

    /**
     * @param Competition $competition
     * @param bool $archive
     * @return CompetitionSeason[]
     */
    public function findByCompetition(Competition $competition, $archive = false)
    {
        return $this->createQueryBuilder('cs')
            ->select('cs')
            ->innerJoin('cs.competition', 'c')
            ->where('cs.competition = :competition')
            ->andWhere('cs.archive = :archive')
            ->setParameter('competition', $competition)
            ->setParameter('archive', $archive)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param bool $archive
     * @return mixed
     */
    public function findAllCompetitionFeatured($archive = false)
    {
        return $this->createQueryBuilder('cs')
            ->select('cs')
            ->innerJoin('cs.competition', 'c')
            ->where('c.isFeatured = :featured')
            ->andWhere('cs.archive = :archive')
            ->setParameter('featured', true)
            ->setParameter('archive', $archive)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Competition $competition
     * @param CompetitionSeason $competitionSeason
     * @param $featured
     * @return CompetitionSeason[]|mixed
     * @throws \Exception
     */
    public function findByConfiguration(
        Competition $competition = null,
        CompetitionSeason $competitionSeason = null,
        $featured = false
    ) {
        switch (true) {
            case ($featured):
                return $this->findAllCompetitionFeatured();
                break;
            case ($competition instanceof Competition):
                return $this->findByCompetition($competition);
                break;
            case ($competitionSeason instanceof CompetitionSeason):
                return $this->findBy(['id' => $competitionSeason]);
                break;
            default:
                return [];
                break;
        }
    }

    // /**
    //  * @return CompetitionSeason[] Returns an array of CompetitionSeason objects
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
    public function findOneBySomeField($value): ?CompetitionSeason
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
