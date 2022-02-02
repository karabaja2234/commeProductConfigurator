<?php

namespace commeProductConfigurator\Core\Content\CommeProductConfiguratorProducts;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(CommeProductConfiguratorProductsEntity $entity)
 * @method void                set(string $key, CommeProductConfiguratorProductsEntity $entity)
 * @method CommeProductConfiguratorProductsEntity[]    getIterator()
 * @method CommeProductConfiguratorProductsEntity[]    getElements()
 * @method CommeProductConfiguratorProductsEntity|null get(string $key)
 * @method CommeProductConfiguratorProductsEntity|null first()
 * @method CommeProductConfiguratorProductsEntity|null last()
 */
class CommeProductConfiguratorProductsCollection extends EntityCollection {
    public function getApiAlias(): string
    {
        return 'comme_product_configurator_products_collection';
    }
    protected function getExpectedClass(): string
    {
        return CommeProductConfiguratorProductsEntity::class;
    }
}
