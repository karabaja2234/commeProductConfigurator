<?php

namespace commeProductConfigurator\Core\Content\Product;

use commeProductConfigurator\Core\Content\CommeProductConfiguratorProducts\CommeProductConfiguratorProductsDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'productConfiguratorParent',
                CommeProductConfiguratorProductsDefinition::class,
                'parent_product_id',
                'id'
            ))->addFlags(new RestrictDelete())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'productConfiguratorChild',
                CommeProductConfiguratorProductsDefinition::class,
                'child_product_id',
                'id'
            ))->addFlags(new RestrictDelete())
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
