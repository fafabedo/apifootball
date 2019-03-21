<?php


namespace App\Service\Crawler;

use App\Service\Config\ConfigManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

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
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * ContentCrawler constructor.
     * @param ManagerRegistry $doctrine
     * @param ConfigManager $configManager
     */
    public function __construct(ManagerRegistry $doctrine,
        ConfigManager $configManager)
    {
        $this->doctrine = $doctrine;
        $this->configManager = $configManager;
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
     * @param ProgressBar $progressBar
     * @return ContentCrawler
     */
    public function setProgressBar(ProgressBar $progressBar): ContentCrawler
    {
        $this->progressBar = $progressBar;
        return $this;
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
        $client = new Client();
        $res = $client->request($method, $this->getPath(), $params);
        $content = $res->getBody()->getContents();
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

}