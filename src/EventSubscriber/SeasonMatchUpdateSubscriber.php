<?php

namespace App\EventSubscriber;

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

        $this
            ->getCompetitionProcessor()
            ->setCompetitions($competitions)
            ->setMatchDay($matchDays)
            ->process()
            ->saveData()
        ;
    }

}
