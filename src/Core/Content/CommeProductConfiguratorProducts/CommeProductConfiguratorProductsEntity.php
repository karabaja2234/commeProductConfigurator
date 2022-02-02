<?php

namespace commeProductConfigurator\Core\Content\CommeProductConfiguratorProducts;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class CommeProductConfiguratorProductsEntity extends Entity {

    use EntityIdTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $parentProductsId;

    /**
     * @var ProductEntity|null
     */
    protected $parentProduct;

    /**
     * @var string
     */
    protected $childProductsId;

    /**
     * @var ProductEntity|null
     */
    protected $childProduct;

    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getParentProductsId(): string
    {
        return $this->parentProductsId;
    }

    /**
     * @param string $parentProductsId
     */
    public function setParentProductsId(string $parentProductsId): void
    {
        $this->parentProductsId = $parentProductsId;
    }

    /**
     * @return ProductEntity|null
     */
    public function getParentProduct(): ?ProductEntity
    {
        return $this->parentProduct;
    }

    /**
     * @param ProductEntity|null $parentProduct
     */
    public function setParentProduct(?ProductEntity $parentProduct): void
    {
        $this->parentProduct = $parentProduct;
    }

    /**
     * @return string
     */
    public function getChildProductsId(): string
    {
        return $this->childProductsId;
    }

    /**
     * @param string $childProductsId
     */
    public function setChildProductsId(string $childProductsId): void
    {
        $this->childProductsId = $childProductsId;
    }

    /**
     * @return ProductEntity|null
     */
    public function getChildProduct(): ?ProductEntity
    {
        return $this->childProduct;
    }

    /**
     * @param ProductEntity|null $childProduct
     */
    public function setChildProduct(?ProductEntity $childProduct): void
    {
        $this->childProduct = $childProduct;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
