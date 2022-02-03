<?php

namespace commeProductConfigurator\Api;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;

/**
 * @RouteScope(scopes={"api"})
 */
class CommeProductConfiguratorController extends AbstractController
{
    public $connection;

    /**
     * @var EntityRepositoryInterface
     */
    private $productConfiguratorProductsRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $seoUrlRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $languageRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    public function __construct(
        Connection $connection,
        EntityRepositoryInterface $productConfiguratorProductsRepository,
        EntityRepositoryInterface $seoUrlRepository,
        EntityRepositoryInterface $languageRepository,
        EntityRepositoryInterface $salesChannelRepository
    ) {
        $this->connection = $connection;
        $this->productConfiguratorProductsRepository = $productConfiguratorProductsRepository;
        $this->seoUrlRepository = $seoUrlRepository;
        $this->languageRepository = $languageRepository;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * @Route("/api/_action/validate-seo-url/{seoPathInfo}", requirements={"seoPathInfo"=".+"}, name="api.action.cpc.validate-seo-url-action", options={"seo"="false"}, methods={"POST", "GET"})
     */
    public function validateSeoUrlAction(Request $request, Context $context) : Response
    {
        $seoPathInfo = $request->attributes->get('seoPathInfo');
        try {
            $criteria = new Criteria();
            $seoUrl = $this->seoUrlRepository->search($criteria->addFilter(new EqualsFilter('seoPathInfo', $seoPathInfo)), $context);
            return new Response(
                strval($seoUrl->getTotal()),
                Response::HTTP_OK,
                ['Content-Type' => 'text/plain']
            );
        } catch(\Exception $e) {
            error_log(print_r(array('err msg', $e->getMessage()), true) . "\n", 3, './error.log');
        }
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/_action/get-seo-url/{entityId}", name="api.action.cpc.get-seo-urls-action", options={"seo"="false"}, methods={"POST", "GET"})
     */
    public function getSeoUrlsAction(Request $request, Context $context) : Response
    {
        $entityId = $request->attributes->get('entityId');
        try {
            $criteria = new Criteria();
            $seoUrl = $this->seoUrlRepository->search($criteria->addFilter(new AndFilter([
                new EqualsFilter('foreignKey', $entityId),
                new EqualsFilter('routeName', "frontend.cpc.product")
            ])), $context)->first();
            return new Response(
                json_encode($seoUrl),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch(\Exception $e) {
            error_log(print_r(array('err msg', $e->getMessage()), true) . "\n", 3, './error.log');
        }
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/_action/validate-parent-product/{parentProductId}", name="api.action.cpc.validate-parent-product-action", options={"seo"="false"}, methods={"POST", "GET"})
     */
    public function validateParentProduct(Request $request, Context $context) : Response
    {
        $parentProductId = $request->attributes->get('parentProductId');
        try {
            $criteria = new Criteria();
            $parentProducts = $this->productConfiguratorProductsRepository->search($criteria->addFilter(new EqualsFilter('parentProductId', $parentProductId)), $context);
            return new Response(
                strval($parentProducts->getTotal()),
                Response::HTTP_OK,
                ['Content-Type' => 'text/plain']
            );
        } catch(\Exception $e) {
            error_log(print_r(array('err msg', $e->getMessage()), true) . "\n", 3, './error.log');
        }
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/_action/save-product/{parentProductId}", name="api.action.cpc.save-product-action", options={"seo"="false"}, methods={"POST", "GET"})
     */
    public function saveOrUpdateProduct(Request $request, Context $context) : Response
    {
        $parentProductId = $request->attributes->get('parentProductId');
        $childProducts = $request->request->get("childProducts");

        try {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('parentProductId', $parentProductId));
            while($this->productConfiguratorProductsRepository->searchIds($criteria, $context)->firstId()) {
                $productId = $this->productConfiguratorProductsRepository->searchIds($criteria, $context)->firstId();
                $this->productConfiguratorProductsRepository->delete([
                    [
                        'id' => $productId
                    ]
                ], $context);
            }

            foreach ($childProducts as $childProductId) {
                $this->productConfiguratorProductsRepository->create([
                    [
                        'id' => Uuid::randomHex(),
                        'parentProductId' => $parentProductId,
                        'childProductId' => $childProductId
                    ]
                ], $context);
            }
        } catch(\Exception $e) {
            error_log(print_r(array('err msg', $e->getMessage()), true) . "\n", 3, './error.log');
        }
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/_action/product-seo-create/{parentProductId}", name="api.action.cpc.product-seo-create-action", options={"seo"="false"}, methods={"POST", "GET"})
     */
    public function createSeo(Request $request, Context $context) : Response
    {
        $parentProductId = $request->attributes->get('parentProductId');

        try {
            $languageCriteria = new Criteria();
            $language = $this->languageRepository->search($languageCriteria->addFilter(new EqualsFilter('name', 'English')), $context)->first();
            $salesChannelCriteria = new Criteria();
            $salesChannel = $this->salesChannelRepository->search($salesChannelCriteria, $context)->first();

            $this->seoUrlRepository->create([
                [
                    'id' => Uuid::randomHex(),
                    'languageId' => $language->getId(),
                    'salesChannelId' => $salesChannel->getId(),
                    'foreignKey' => $parentProductId,
                    'routeName' => 'frontend.cpc.product',
                    'pathInfo' => '/cpc/' . $parentProductId,
                    'seoPathInfo' => Uuid::randomHex(),
                    'isCanonical' => true
                ]
            ], $context);

        } catch(\Exception $e) {
            error_log(print_r(array('err msg', $e->getMessage()), true) . "\n", 3, './error.log');
        }
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
