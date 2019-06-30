<?php


namespace App\Service\ProcessQueue;


use App\Entity\ProcessQueueMedia;
use App\Service\Media\MediaManager;
use Doctrine\Common\Persistence\ManagerRegistry;

class ProcessQueueMediaManager
{
    private $mediaManager;

    private $doctrine;

    /**
     * ProcessQueueMediaManager constructor.
     * @param ManagerRegistry $doctrine
     * @param MediaManager $mediaManager
     */
    public function __construct(ManagerRegistry $doctrine, MediaManager $mediaManager)
    {
        $this->doctrine = $doctrine;
        $this->mediaManager = $mediaManager;
    }

    /**
     * @return MediaManager
     */
    protected function getMediaManager(): MediaManager
    {
        return $this->mediaManager;
    }

    /**
     * @return ManagerRegistry
     */
    protected function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * @param $filename
     * @param $sourceUrl
     * @return $this
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function add($filename, $sourceUrl)
    {
        if (empty($sourceUrl)) {
            return $this;
        }
        $processQueueMedia = $this->getDoctrine()
            ->getRepository(ProcessQueueMedia::class)
            ->findByFilename($filename);

        if (!$processQueueMedia instanceof ProcessQueueMedia) {
            $processQueueMedia = new ProcessQueueMedia();
            $processQueueMedia->setFilename($filename);
            $processQueueMedia->setSourceUrl($sourceUrl);
            $processQueueMedia->setStatus(ProcessQueueMedia::STATUS_PENDING);
            $em = $this->getDoctrine()->getManager();
            $em->persist($processQueueMedia);
            $em->flush();
        }
        return $this;
    }

    /**
     * @param $filename
     * @return $this
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function remove($filename) {
        $processQueueMedia = $this->getDoctrine()
            ->getRepository(ProcessQueueMedia::class)
            ->findByFilename($filename);
        if (!$processQueueMedia instanceof ProcessQueueMedia) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($processQueueMedia);
            $em->flush();
        }
        return $this;
    }

    /**
     * @param ProcessQueueMedia $processQueueMedia
     */
    public function save(ProcessQueueMedia $processQueueMedia)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($processQueueMedia);
        $em->flush();
    }

    /**
     * @return ProcessQueueMedia[]|object[]
     */
    public function getPendingMedia()
    {
        return $this->getDoctrine()
            ->getRepository(ProcessQueueMedia::class)
            ->findBy(['status' => ProcessQueueMedia::STATUS_PENDING]);
    }

}
