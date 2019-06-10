<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CachePageRepository")
 */
class CachePage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cacheId;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $expire;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $collection;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lifetime;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pathUrl;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCacheId(): ?string
    {
        return $this->cacheId;
    }

    /**
     * @param string $cacheId
     * @return CachePage
     */
    public function setCacheId(string $cacheId): self
    {
        $this->cacheId = $cacheId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string|null $data
     * @return CachePage
     */
    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param \DateTimeInterface|null $created
     * @return CachePage
     */
    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getExpire(): ?bool
    {
        return $this->expire;
    }

    /**
     * @param bool|null $expire
     * @return CachePage
     */
    public function setExpire(?bool $expire): self
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCollection(): ?string
    {
        return $this->collection;
    }

    /**
     * @param string|null $collection
     * @return CachePage
     */
    public function setCollection(?string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLifetime(): ?int
    {
        return $this->lifetime;
    }

    /**
     * @param int|null $lifetime
     * @return CachePage
     */
    public function setLifetime(?int $lifetime): self
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPathUrl(): ?string
    {
        return $this->pathUrl;
    }

    /**
     * @param string|null $pathUrl
     * @return CachePage
     */
    public function setPathUrl(?string $pathUrl): self
    {
        $this->pathUrl = $pathUrl;

        return $this;
    }

}
