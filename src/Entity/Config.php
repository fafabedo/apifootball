<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\ConfigRepository")
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "collection": "partial", "name": "partial", "data": "partial"})
 * @ApiFilter(OrderFilter::class, properties={"name", "ASC", "collection", "ASC"})
 */
class Config
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $collection;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="array")
     */
    private $data;

  /**
   * @return int|null
   * @Groups({"read"})
   */
  public function getId(): ?int
    {
        return $this->id;
    }

  /**
   * @return string|null
   * @Groups({"read"})
   */
  public function getCollection(): ?string
    {
        return $this->collection;
    }

  /**
   * @param string|null $collection
   *
   * @return \App\Entity\Config
   */
  public function setCollection(?string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

  /**
   * @return string|null
   * @Groups({"read"})
   */
  public function getName(): ?string
    {
        return $this->name;
    }

  /**
   * @param string $name
   *
   * @return \App\Entity\Config
   */
  public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

  /**
   * @return mixed
   * @Groups({"read"})
   */
  public function getData()
    {
        return $this->data;
    }

  /**
   * @param $data
   *
   * @return \App\Entity\Config
   */
  public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
