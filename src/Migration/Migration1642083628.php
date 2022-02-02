<?php declare(strict_types=1);

namespace commeProductConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1642083628 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1642083628;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE IF NOT EXISTS `comme_product_configurator_products` (
                `id` BINARY(16) NOT NULL,
                `parent_product_id` BINARY(16) NOT NULL,
                `child_product_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.comme_product_configurator_products.parent_product_id` FOREIGN KEY (`parent_product_id`)
                    REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.comme_product_configurator_products.child_product_id` FOREIGN KEY (`child_product_id`)
                    REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
