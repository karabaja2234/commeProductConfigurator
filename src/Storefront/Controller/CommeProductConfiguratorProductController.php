<?php

namespace commeProductConfigurator\Storefront\Controller;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;

class CommeProductConfiguratorProductController extends StorefrontController
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var GenericPageLoader
     */
    private $pageLoader;

    /**
     * @var EntityRepositoryInterface
     */
    private $productConfiguratorProductsRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Connection $connection,
        GenericPageLoader $pageLoader,
        EntityRepositoryInterface $productConfiguratorProductsRepository,
        EntityRepositoryInterface $productRepository
    )
    {
        $this->connection = $connection;
        $this->pageLoader = $pageLoader;
        $this->productConfiguratorProductsRepository = $productConfiguratorProductsRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/cpc/{parentProductId}", name="frontend.cpc.product", methods={"GET"}, defaults={"XmlHttpRequest": true})
     */
    public function index(SalesChannelContext $salesChannelContext, Request $request, Context $context): Response
    {
        $parentProductId = $request->attributes->get('parentProductId');
        $page = $this->pageLoader->load($request, $salesChannelContext);

        try {
            $productsCriteria = new Criteria();
            $productsCriteria->addFilter(new EqualsFilter('id', $parentProductId));
            $productsCriteria->addAssociation('translations');
            $parentProduct = $this->productRepository->search($productsCriteria, $context)->getEntities()->first();

            $productConfiguratorChildProductsCriteria = new Criteria();
            $productConfiguratorChildProductsCriteria->addAssociation('product');
            $productConfiguratorChildProductsCriteria->addFilter(new EqualsFilter('parentProductId', $parentProductId));
            $productConfiguratorChildProducts = $this->productConfiguratorProductsRepository->search($productConfiguratorChildProductsCriteria, $context)->getEntities();
            $childProductsIds = [];
            foreach ($productConfiguratorChildProducts as $childProduct) {
                array_push($childProductsIds, $childProduct->childProductId);
            }
            $childProductsCriteria = new Criteria($childProductsIds);
            $childProductsCriteria->addAssociation('translations');
            $childProducts = $this->productRepository->search($childProductsCriteria, $context)->getEntities();

            if($parentProduct) {
                return $this->renderStorefront('@Storefront/plugin/comme-product-configurator/page/comme-product-configurator-products.html.twig', ['page' => $page, 'parentProduct' => $parentProduct, 'childProducts' => $childProducts]);
            }
            return new Response("Product does not exist or it is inactive", Response::HTTP_NOT_FOUND);
        } catch(\Exception $e) {
            error_log(print_r(array('err msg', $e->getMessage()), true) . "\n", 3, './error.log');
        }
        return new Response(null, Response::HTTP_NOT_FOUND);
    }
}
