<?php

namespace App\Service\Crawler\Entity\Match;

class MatchLineupPlayer
{
    private $code;

    private $number;

    private $top;

    private $left;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return MatchLineupPlayer
     */
    public function setCode($code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     * @return MatchLineupPlayer
     */
    public function setNumber($number): self
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * @param mixed $top
     * @return MatchLineupPlayer
     */
    public function setTop($top): self
    {
        $this->top = $top;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param mixed $left
     * @return MatchLineupPlayer
     */
    public function setLeft($left): self
    {
        $this->left = $left;
        return $this;
    }



}
