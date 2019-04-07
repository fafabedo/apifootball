<?php


namespace App\Tool;

/**
 * Class DateTimeTool
 * @package App\Tool
 */
class DateTimeTool
{
    /**
     * @param \DateTime $dateTime
     * @param $timeText
     * @return \DateTime
     */
    static public function setTextTimeToDateTime(\DateTime $dateTime, $timeText)
    {
        if (preg_match('/([0-9]+):([0-9]+).(PM|AM)/', $timeText, $matches)) {
            if (isset($matches[1]) && isset($matches[2])) {
                switch (true) {
                    case (isset($matches[3]) && $matches[3] === 'PM'):
                        $hours = (int) $matches[1] + 12;
                        $dateTime->setTime($hours, $matches[2]);
                        break;
                    default:
                        $dateTime->setTime($matches[1], $matches[2]);
                        break;
                }
            }
        }
        return $dateTime;
    }

    /**
     * @param $dateText
     * @return \DateTime
     * @throws \Exception
     */
    static public function createDateTime($dateText): \DateTime
    {
        return (new \DateTime($dateText));
    }
}
