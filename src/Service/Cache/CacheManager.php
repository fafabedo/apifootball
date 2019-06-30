<?php


namespace App\Service\Cache;


use App\Entity\CachePage;
use App\Tool\HtmlTool;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class CacheManager
 * @package App\Service\Cache
 */
class CacheManager
{
    public const DEFAULT_LIFETIME = 2592000;
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    private $lifetime = self::DEFAULT_LIFETIME;

    /**
     * CacheManager constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * @param int $lifetime
     * @return CacheManager
     */
    public function setLifetime(int $lifetime): CacheManager
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    /**
     * @param $path
     * @param $method
     * @param $params
     * @return string|null
     */
    public function generateCacheId($path, $method, $params): ?string
    {
        $elements = [$path, $method, $params];
        $text = serialize($elements);
        $cid = md5($text);
        return $cid;
    }

    /**
     * @param $cid
     * @return string|null
     */
    public function getPageCache($cid): ?string
    {
        $page = $this
            ->getDoctrine()
            ->getRepository(CachePage::class)
            ->findOneBy(['cacheId' => $cid]);

        if (!$page instanceof CachePage) {
            return null;
        }
        return $page->getData();
    }

    /**
     * @param $cid
     * @param $path
     * @param $content
     * @return CacheManager
     * @throws \Exception
     */
    public function setPageCache($cid, $path, $content)
    {
        if ($content === null) {
            return $this;
        }
        $content = HtmlTool::trimHtml($content);
        $cachePage = new CachePage();
        $cachePage->setCacheId($cid);
        $cachePage->setPathUrl($path);
        $encoding = mb_detect_encoding($content);
        if ($encoding !== 'UTF-8') {
            $content = utf8_encode($content);
        }
        $cachePage->setData($content);
        $cachePage->setExpire(false);
        $cachePage->setLifetime($this->getLifetime());
        $created = new \DateTime();
        $cachePage->setCreated($created);
        $em = $this->getDoctrine()->getManager();
        $em->persist($cachePage);
        $em->flush();
        return $this;
    }
}
