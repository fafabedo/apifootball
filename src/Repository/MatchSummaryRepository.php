<?php

namespace App\Repository;

use App\Entity\MatchSummary;
use App\Traits\TmkEntityTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MatchSummary|null find($id, $lockMode = null, $lockVersion = null)
 * @method MatchSummary|null findOneBy(array $criteria, array $orderBy = null)
 * @method MatchSummary[]    findAll()
 * @method MatchSummary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchSummaryRepository extends ServiceEntityRepository
{
    use TmkEntityTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MatchSummary::class);
    }

    /**
     * @param bool $featured
     * @return MatchSummary[] Returns an array of MatchSummary objects
     */
    public function findByFeaturedCompetition($featured = true)
    {
        return $this->createQueryBuilder('ms')
            ->leftJoin('ms.competitionSeasonMatch', 'csm')
            ->leftJoin('csm.competition_season', 'cs')
            ->leftJoin('cs.competition', 'c')
            ->select(['ms', 'csm'])
            ->where('c.isFeatured = :featured')
            ->setParameter('featured', $featured)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $competitionId
     * @param bool $archive
     * @return MatchSummary[] Returns an array of MatchSummary objects
     */
    public function findByCompetitionId($competitionId, $archive = false)
    {
        return $this->createQueryBuilder('ms')
            ->leftJoin('ms.competitionSeasonMatch', 'csm')
            ->leftJoin('csm.competition_season', 'cs')
            ->leftJoin('cs.competition', 'c')
            ->select(['ms', 'csm'])
            ->where('c.id = :competitionId')
            ->andWhere('cs.archive = :archive')
            ->setParameter('competitionId', $competitionId)
            ->setParameter('archive', $archive)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $competitionMatchId
     * @return MatchSummary Returns an array of MatchSummary objects
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByCompetitionMatchId($competitionMatchId)
    {
        return $this->createQueryBuilder('ms')
            ->leftJoin('ms.competitionSeasonMatch', 'csm')
            ->select(['ms', 'csm'])
            ->where('csm.id = :competitionMatchId')
            ->setParameter('competitionMatchId', $competitionMatchId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
