<?php

namespace App\Service\Media;

use Doctrine\Common\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use Symfony\Component\HttpKernel\KernelInterface;

class MediaManager
{
    public const DEFAULT_EXT = 'jpg';

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * MediaManager constructor.
     * @param KernelInterface $kernel
     * @param ManagerRegistry $doctrine
     */
    public function __construct(KernelInterface $kernel, ManagerRegistry $doctrine)
    {
        $this->kernel = $kernel;
        $this->doctrine = $doctrine;
    }

    /**
     * @return KernelInterface
     */
    protected function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    /**
     * @return ManagerRegistry
     */
    protected function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @param $sourceUrl
     * @param $destination
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function persistMedia($sourceUrl, $destination)
    {
        try {
            $filenameFilesystem = $this->getKernel()->getProjectDir() . $destination;
            if (file_exists($filenameFilesystem)) {
                return true;
            }
            $client = new Client();
            $client->request('GET', $sourceUrl, [
                'sink' => $filenameFilesystem
            ]);
        }
        catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @param $url
     * @return string
     */
    public function getExtension($url): string
    {
        if (preg_match('/\.(...)($|\?)/', $url, $matches)) {
            return $matches[1];
        }
        return MediaManager::DEFAULT_EXT;
    }

    /**
     * @param $sourceUrl
     * @return mixed|null
     */
    public function getFilenameFromUrl($sourceUrl)
    {
        if (preg_match('/http[\s]*:\/\/.+\/([^\/|^.]+\....)[?]*/i', $sourceUrl, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @param string $folder
     * @param string $name
     * @param string $ext
     * @return string
     */
    public function getRelativeFilename($folder, $name, $ext = self::DEFAULT_EXT)
    {
        $filename = "/$name.$ext";
        if (empty($folder)) {
            return $filename;
        }
        return "/$folder$filename";
    }

}
