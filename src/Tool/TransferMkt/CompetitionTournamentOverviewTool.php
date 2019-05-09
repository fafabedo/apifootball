<?php


namespace App\Tool\TransferMkt;


class CompetitionTournamentOverviewTool
{
    /**
     * @param $htmlCode
     * @return mixed|null
     */
    static public function getSpanTeamTmkCode($htmlCode)
    {
        if (preg_match('/id="([^"]+)".href="([^"]+)"/i', $htmlCode, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
