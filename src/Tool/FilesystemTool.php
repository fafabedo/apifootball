<?php


namespace App\Tool;

use GuzzleHttp\Client;

/**
 * Class FilesystemTool
 * @package App\Tool
 */
class FilesystemTool
{
    public const PUBLIC_FILES_FOLDER = '/public/files';
    public const DEFAULT_EXT = 'jpg';
    public const SOURCE = 'source';
    public const DESTINATION = 'destination';

    /**
     * @param $url
     * @param $destination
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    static public function persistFile($url, $destination): bool
    {
        try {
            $client = new Client();
            $client->request('GET', $url, [
                'sink' => $destination
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
    static public function getExtension($url): string
    {
        preg_match('/\.(...)($|\?)/', $url, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return self::DEFAULT_EXT;
    }

    /**
     * @param $subdirectory
     * @param $filename
     * @param string $ext
     * @return string
     */
    static public function getFilename($subdirectory, $filename, $ext = self::DEFAULT_EXT)
    {
        return "/$subdirectory/$filename.$ext";
    }

    /**
     * @param $rootFolder
     * @param $subdirectory
     * @param $filename
     * @param string $ext
     * @return string
     */
    static public function getDestination($rootFolder, $subdirectory, $filename, $ext = self::DEFAULT_EXT): string
    {
        $folder = $rootFolder
            . self::PUBLIC_FILES_FOLDER
            . "/$subdirectory";
        if (file_exists($folder) === false) {
            mkdir($folder);
        }
        return $rootFolder
            . self::PUBLIC_FILES_FOLDER
            . self::getFilename($subdirectory, $filename, $ext);
    }

    /**
     * @param array $collection
     * @param $value
     * @param string $type
     * @return int|false
     */
    static public function findIndexByFilename(array $collection, $value, $type = self::DESTINATION)
    {
        foreach ($collection as $index => $item) {
            if ($item[$type] === $value) {
                return $index;
            }
        }
        return false;
    }
}
