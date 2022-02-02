<?php declare(strict_types=1);

namespace commeProductConfigurator;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class commeProductConfigurator extends Plugin
{
    public function install(InstallContext $context): void
    {
        parent::install($context);
    }

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }
        $connection = $this->container->get(Connection::class);

//        $connection->executeUpdate('SET FOREIGN_KEY_CHECKS=0');
//        $connection->executeUpdate('DROP TABLE IF EXISTS `comme_product_configurator_products`');
//        $connection->executeUpdate('SET FOREIGN_KEY_CHECKS=1');
    }
}
