<?php


namespace App\Service\Formula;


use App\Entity\CompetitionSeason;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class AbstractFormula
 * @package App\Service\Formula
 */
abstract class AbstractFormula implements FormulaInterface
{
    /**
     * @var CompetitionSeason
     */
    private $competitionSeason;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var string
     */
    private $formula;

    /**
     * AbstractFormula constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @param CompetitionSeason $competitionSeason
     * @return $this
     */
    public function setCompetitionSeason(CompetitionSeason $competitionSeason)
    {
        $this->competitionSeason = $competitionSeason;
        return $this;
    }

    /**
     * @return CompetitionSeason
     */
    public function getCompetitionSeason()
    {
        return $this->competitionSeason;
    }

    /**
     * @return string
     */
    public function getFormula(): string
    {
        return $this->formula;
    }

    /**
     * @param string $formula
     * @return AbstractFormula
     */
    public function setFormula($formula): AbstractFormula
    {
        $this->formula = $formula;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setParameter($name, $value) {
        $method = 'set' . Inflector::camelize($name);
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }

    abstract public function validateFormula($formula);
}
