<?php


namespace App\Traits;


trait TmkEntityRepositoryTrait
{
    public function findOneByTmkCode($tmkCode)
    {
        return $this->findOneBy(['tmkCode' => $tmkCode]);
    }

    public function findByTmkCode($tmkCodes)
    {
        return $this->findBy(['tmkCode' => $tmkCodes]);
    }
}
