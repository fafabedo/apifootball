<?php

namespace App\EventSubscriber;

use App\Entity\Competition;
use App\Entity\CompetitionType;
use App\Event\CompetitionSeasonMatchEvent;
use App\Service\Processor\Competition\CompetitionProcessor;
use App\Service\Processor\Table\TableStandardProcessor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SeasonMatchUpdateSubscriber
 * @package App\EventSubscriber
 */
class SeasonMatchUpdateSubscriber implements EventSubscriberInterface
{
    const TOURNAMENT = 1;
    const LEAGUE = 2;

    /**
     * @var CompetitionProcessor
     */
    private $competitionProcessor;

    /**
     * SeasonMatchUpdateSubscriber constructor.
     * @param CompetitionProcessor $competitionProcessor
     */
    public function __construct(CompetitionProcessor $competitionProcessor)
    {
        $this->competitionProcessor = $competitionProcessor;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CompetitionSeasonMatchEvent::POST_UPDATE=> ['postMatchUpdate', 1],
        ];
    }

    /**
     * @return CompetitionProcessor
     */
    public function getCompetitionProcessor(): CompetitionProcessor
    {
        return $this->competitionProcessor;
    }

    /**
     * @param CompetitionSeasonMatchEvent $event
     * @return $this
     * @throws \Exception
     */
    public function postMatchUpdate(CompetitionSeasonMatchEvent $event)
    {
        if (empty($event->getCompetitionMatches())) {
            return $this;
        }
        $matchDays = [];
        $competitions = [];
        foreach ($event->getCompetitionMatches() as $match) {
            if (!in_array($match->getMatchDay(), $matchDays)) {
                $matchDays[] = $match->getMatchDay();
            }
            if (!in_array($match->getCompetitionSeason()->getCompetition(), $competitions)) {
                $competitions[] = $match->getCompetitionSeason()->getCompetition();
            }
        }

        /* @var Competition $competition*/
        foreach ($competitions as $competition) {
            $competitionTypeId = $competition
                ->getCompetitionType()
                ->getId();
            switch ($competitionTypeId) {
                case CompetitionType::TOURNAMENT:
                    $this
                        ->getCompetitionProcessor()
                        ->setCompetitions([$competition])
                        ->process()
                        ->saveData()
                    ;
                    break;
                default:
                case CompetitionType::LEAGUE:
                    $this
                        ->getCompetitionProcessor()
                        ->setCompetitions([$competition])
                        ->setMatchDay($matchDays)
                        ->process()
                        ->saveData()
                    ;
                    break;
            }

        }
    }

}
