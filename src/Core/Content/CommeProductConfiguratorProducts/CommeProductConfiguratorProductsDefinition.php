<?php

namespace commeProductConfigurator\Core\Content\CommeProductConfiguratorProducts;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CommeProductConfiguratorProductsDefinition
    extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'comme_product_configurator_products';
    }

    public function getCollectionClass(): string
    {
        return CommeProductConfiguratorProductsCollection::class;
    }

    public function getEntityClass(): string
    {
        return CommeProductConfiguratorProductsEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
                (new FkField('parent_product_id', 'parentProductId', ProductDefinition::class))->addFlags(new Required()),
                (new FkField('child_product_id', 'childProductId', ProductDefinition::class))->addFlags(new Required()),
                new ManyToOneAssociationField('parentProduct', 'parent_product_id', ProductDefinition::class, 'id', false),
                new ManyToOneAssociationField('childProduct', 'child_product_id', ProductDefinition::class, 'id', false)
            ]
        );
    }
}
