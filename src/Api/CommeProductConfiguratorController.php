<?php

namespace commeProductConfigurator\Api;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
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

    public function __construct(
        Connection $connection,
        EntityRepositoryInterface $productConfiguratorProductsRepository,
        EntityRepositoryInterface $seoUrlRepository
    ) {
        $this->connection = $connection;
        $this->productConfiguratorProductsRepository = $productConfiguratorProductsRepository;
        $this->seoUrlRepository = $seoUrlRepository;
    }

    /**
     * @Route("/api/_action/validate-seo-url/{seoPathInfo}", requirements={"seoPathInfo"=".+"}, name="api.action.cpc.validate-seo-url-action", options={"seo"="false"}, methods={"GET"})
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
     * @Route("/api/_action/get-seo-url/{entityId}", name="api.action.cpc.get-seo-urls-action", options={"seo"="false"}, methods={"GET"})
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
}
