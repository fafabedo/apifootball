<?php


namespace App\Service\Config;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class ConfigManager
 * @package App\Service\Config
 */
class ConfigManager
{
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * ConfigManager constructor.
     * @param ConfigRepository $configRepository
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ConfigRepository $configRepository, ManagerRegistry $doctrine)
    {
        $this->configRepository = $configRepository;
        $this->doctrine = $doctrine;
    }

    /**
     * @return ConfigRepository
     */
    public function getConfigRepository(): ConfigRepository
    {
        return $this->configRepository;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getValue($name): ?array
    {
        $config = $this
            ->getConfigRepository()
            ->findOneBy(['name' =>$name])
        ;
        if ($config instanceof Config) {
            return $config->getData();
        }
        return null;
    }

    /**
     * @param $name
     * @param $data
     * @return $this
     */
    public function setValue($name, $data)
    {
        $config = $this
            ->getConfigRepository()
            ->findOneBy(['name' =>$name])
        ;
        if (!$config instanceof Config) {
            $config = new Config();
            $config->setName($name);
        }
        $config->setData($data);

        $this->getDoctrine()
            ->getManager()
            ->persist($config);
        return $this;

    }

}