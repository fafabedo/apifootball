<?php


namespace App\Service\Cache;


/**
 * Class CacheLifetime
 * @package App\Service\Cache
 */
class CacheLifetime
{
    public const CACHE_COUNTRY = 'cache_country';
    public const CACHE_COMPETITION = 'cache_competition';
    public const CACHE_COMPETITION_SEASON = 'cache_competition_season';
    public const CACHE_COMPETITION_MATCH = 'cache_competition_match';
    public const CACHE_COMPETITION_TABLE = 'cache_competition_table';
    public const CACHE_TEAM = 'cache_team';
    public const CACHE_PLAYER = 'cache_player';
    public const CACHE_COACH_STAFF = 'cache_coach_staff';

    private const LIFETIME_BASE = 2592000;

    /**
     * @var array
     */
    private $options = array(
        self::CACHE_COUNTRY => self::LIFETIME_BASE,
        self::CACHE_COMPETITION => self::LIFETIME_BASE,
        self::CACHE_COMPETITION_SEASON => self::LIFETIME_BASE,
        self::CACHE_COMPETITION_MATCH => self::LIFETIME_BASE,
        self::CACHE_COMPETITION_TABLE => self::LIFETIME_BASE,
        self::CACHE_TEAM => self::LIFETIME_BASE,
        self::CACHE_PLAYER => self::LIFETIME_BASE,
        self::CACHE_COACH_STAFF => self::LIFETIME_BASE,
    );

    /**
     * CacheLifetime constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param $name
     * @return int
     */
    public function getLifetime($name): int
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
        return self::LIFETIME_BASE;
    }


}
