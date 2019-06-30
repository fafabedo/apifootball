<?php


namespace App\Service\Request;


use App\Entity\CachePage;
use App\Service\Cache\CacheLifetime;
use App\Service\Cache\CacheManager;
use App\Tool\HtmlTool;
use Doctrine\Common\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Class RequestService
 * @package App\Service\Request
 */
class RequestService
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var CacheLifetime
     */
    private $cacheLifetime;

    /**
     * RequestService constructor.
     * @param CacheManager $cacheManager
     * @param CacheLifetime $cacheLifetime
     */
    public function __construct(
        CacheManager $cacheManager,
        CacheLifetime $cacheLifetime
    ) {
        $this->cacheManager = $cacheManager;
        $this->cacheLifetime = $cacheLifetime;
    }

    /**
     * @return CacheManager
     */
    public function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }

    /**
     * @return CacheLifetime
     */
    public function getCacheLifetime(): CacheLifetime
    {
        return $this->cacheLifetime;
    }

    public function getLifetime($name): int
    {
        return $this
            ->getCacheLifetime()
            ->getLifetime($name)
            ;
    }

    /**
     * @param $lifetime
     * @return RequestService
     */
    public function setLifetime($lifetime): RequestService
    {
        $this
            ->getCacheManager()
            ->setLifetime($lifetime);
        return $this;
    }

    /**
     * @param $path
     * @param string $method
     * @param array $params
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getContent($path, $method = 'GET', $params = []): ?string
    {
        $cid = $this
            ->getCacheManager()
            ->generateCacheId($path, $method, $params);
        $data = $this
            ->getCacheManager()
            ->getPageCache($cid);
        if ($data !== null) {
            return $data;
        }

        try {
            $content = $this->request($path, $method, $params);
            $this
                ->getCacheManager()
                ->setPageCache($cid, $path, $content);
        } catch (\Exception $e) {
            $content = null;
        }
        return $content;
    }

    /**
     * @param $path
     * @param string $method
     * @param array $params
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request($path, $method = 'GET', $params = []): ?string
    {
        $client = new Client();
        $res = $client->request($method, $path, $params);
        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Unsuccessful response http code');
        }
        $content = $res->getBody()->getContents();
        return $content;
    }

}
