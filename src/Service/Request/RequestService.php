<?php


namespace App\Service\Request;


use App\Entity\CachePage;
use App\Tool\HtmlTool;
use Doctrine\Common\Persistence\ManagerRegistry;
use GuzzleHttp\Client;

/**
 * Class RequestService
 * @package App\Service\Request
 */
class RequestService
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * RequestService constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return ManagerRegistry
     */
    private function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
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
        $cid = $this->generateCacheId($path, $method, $params);
        $data = $this->getPageCache($cid);
        if ($data !== null) {
            return $data;
        }

        try {
            $content = $this->request($path, $method, $params);
            $this->setPageCache($cid, $content);
        }
        catch (\Exception $e) {
            $content = '';
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
        $content = $res->getBody()->getContents();
        return $content;
    }

    /**
     * @param $path
     * @param $method
     * @param $params
     * @return string|null
     */
    private function generateCacheId($path, $method, $params): ?string
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
    private function getPageCache($cid): ?string
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
     * @param $content
     * @throws \Exception
     */
    private function setPageCache($cid, $content): void
    {
        $content = HtmlTool::trimHtml($content);
        $cachePage = new CachePage();
        $cachePage->setCacheId($cid);
        $cachePage->setData($content);
        $cachePage->setExpire(false);
        $created = new \DateTime();
        $cachePage->setCreated($created);
        $em = $this->getDoctrine()->getManager();
        $em->persist($cachePage);
        $em->flush();
    }


}
