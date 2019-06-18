<?php


namespace App\Service\Crawler;

use App\Service\Cache\CacheLifetime;
use App\Service\Cache\CacheManager;
use App\Service\Config\ConfigManager;
use App\Service\Metadata\MetadataSchemaResources;
use App\Service\Request\RequestService;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ContentCrawler
 * @package App\Service\Crawler
 */
abstract class ContentCrawler implements CrawlerInterface
{

    /**
     * @var string
     */
    private $content;

    /**
     * @var $string
     */
    private $path;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var RequestService
     */
    private $requestService;

    /**
     * @var CacheLifetime
     */
    private $cacheLifetime;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $rootFolder;

    /**
     * @var MetadataSchemaResources
     */
    private $metadataSchema;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var integer
     */
    private $limit = 200;

    /**
     * @var integer
     */
    private $offset = 1;

    /**
     * @var int
     */
    private $lifetime = CacheManager::DEFAULT_LIFETIME;

    /**
     * ContentCrawler constructor.
     * @param ManagerRegistry $doctrine
     * @param ConfigManager $configManager
     * @param RequestService $requestService
     * @param KernelInterface $kernel
     * @param MetadataSchemaResources $metadataSchema
     * @param CacheLifetime $cacheLifetime
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ManagerRegistry $doctrine,
        ConfigManager $configManager,
        RequestService $requestService,
        KernelInterface $kernel,
        MetadataSchemaResources $metadataSchema,
        CacheLifetime $cacheLifetime,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->doctrine = $doctrine;
        $this->configManager = $configManager;
        $this->requestService = $requestService;
        $this->metadataSchema = $metadataSchema;
        $this->cacheLifetime = $cacheLifetime;
        $this->eventDispatcher = $eventDispatcher;
        $this->rootFolder = $kernel->getProjectDir();
        $this->crawler = new Crawler();
    }


    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }

    /**
     * @return RequestService
     */
    public function getRequestService(): RequestService
    {
        return $this->requestService;
    }

    /**
     * @return MetadataSchemaResources
     * @throws \App\Exception\InvalidMetadataSchema
     */
    public function getMetadataSchema(): MetadataSchemaResources
    {
        return MetadataSchemaResources::createSchema();
    }

    /**
     * @return CacheLifetime
     */
    public function getCacheLifetime(): CacheLifetime
    {
        return $this->cacheLifetime;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @return OutputInterface|null
     */
    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     * @return ContentCrawler
     */
    public function setOutput(OutputInterface $output): ContentCrawler
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return ProgressBar
     */
    public function getProgressBar(): ?ProgressBar
    {
        return $this->progressBar;
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
     * @return ContentCrawler
     */
    public function setLifetime(int $lifetime): ContentCrawler
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return ContentCrawler
     */
    public function setLimit($limit): CrawlerInterface
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return ContentCrawler
     */
    public function setOffset($offset): CrawlerInterface
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param ProgressBar $progressBar
     * @return ContentCrawler
     */
    public function setProgressBar(ProgressBar $progressBar): ContentCrawler
    {
        $this->progressBar = $progressBar;
        return $this;
    }

    /**
     * @return string
     */
    public function getRootFolder(): string
    {
        return $this->rootFolder;
    }

    /**
     * @param string $title
     * @param int $steps
     * @return $this
     */
    public function createProgressBar($title = '', $steps = 1)
    {
        if ($this->getOutput() instanceof OutputInterface) {
            $this->getOutput()->writeln('');
            $this->getOutput()->writeln($title);
            $progressBar = new ProgressBar($this->getOutput(), $steps);
            $this->setProgressBar($progressBar);
        }
        return $this;
    }

    /**
     * @param int $steps
     * @return $this
     */
    public function advanceProgressBar($steps = 1)
    {
        if ($this->getOutput() instanceof OutputInterface) {
            $this->getProgressBar()->advance($steps);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function finishProgressBar()
    {
        if ($this->getOutput() instanceof OutputInterface) {
            $this->getProgressBar()->finish();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return ContentCrawler
     */
    public function setContent(string $content): ContentCrawler
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return Crawler
     */
    public function getCrawler(): Crawler
    {
        return $this->crawler;
    }

    /**
     * @return string|null
     */
    public function getPath() :?string
    {
        return $this->path;
    }

    /**
     * @param string $method
     * @param array $params
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function executeThenGetContent($method = 'GET', $params = []): ?string
    {
        $content = $this
            ->getRequestService()
            ->setLifetime($this->getLifetime())
            ->getContent($this->getPath(), $method, $params);
        $this->setContent($content);

        return $this->getContent();
    }

    /**
     * @param $path
     * @param string $method
     * @param array $params
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processPath($path, $method = 'GET', $params = [])
    {
        $this->path = $path;
        $content = $this->executeThenGetContent($method, $params);
        $this->getCrawler()->clear();
        $this->getCrawler()->add($content);
        return $this;
    }

    /**
     * @param string $path
     * @param array $vars
     * @return string
     */
    public function preparePath($path, $vars = [])
    {
        foreach ($vars as $key => $value) {
            $pattern = '/(\?' . ($key + 1) . ')/';
            $path = preg_replace($pattern, $value, $path);
        }
        return $path;
    }


    /**
     * @param $name
     * @return MetadataSchemaResources|null
     * @throws \App\Exception\InvalidMetadataSchema
     */
    public function getConfigSchema($name): ?MetadataSchemaResources
    {
        $config = $this
            ->getConfigManager()
            ->getValue($name);
        if (!is_array($config)) {
            return null;
        }
        return MetadataSchemaResources::createSchema($config);
    }

    /**
     * @param $pattern
     * @return Crawler|null
     */
    public function filter($pattern): ?Crawler
    {
        return $this->getCrawler()
            ->filterXPath($pattern);
    }

    abstract public function process(): CrawlerInterface;

    abstract public function getData();

    abstract public function saveData(): CrawlerInterface;

    /**
     * @param $name
     * @param $id
     * @return MetadataSchemaResources|null
     * @throws \App\Exception\InvalidMetadataSchema
     */
    protected function getSchemaResource($name, $id)
    {
        $overrideName = $name . '.override';
        $overrideConfig = $this
            ->getConfigManager()
            ->getValue($overrideName);
        if ($overrideConfig === null) {
            return $this->getConfigSchema($name);
        }

        $metadata = new MetadataSchemaResources();
        if (isset($overrideConfig[$id])) {
            $metadata->setSchema($overrideConfig[$id]);
        }

        return $metadata;
    }

    /**
     * @param $index
     * @return bool
     */
    protected function validOffset($index)
    {
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $ceiling = $offset + $limit;
        switch(true) {
            case ($offset <= $index && $index < $ceiling):
                return true;
                break;
            default:
                return false;
                break;
        }
    }

}
