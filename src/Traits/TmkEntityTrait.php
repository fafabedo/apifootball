<?php


namespace App\Traits;

trait TmkEntityTrait
{
    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $tmkCode;

    /**
     * @return string|null
     */
    public function getTmkCode(): ?string
    {
        return $this->tmkCode;
    }

    /**
     * @param string|null $tmkCode
     * @return $this
     */
    public function setTmkCode(?string $tmkCode)
    {
        $this->tmkCode = $tmkCode;
        return $this;
    }

}
