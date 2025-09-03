<?php declare(strict_types=1);

namespace AltOffCanvas\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CrossSellingService
{
    private EntityRepository $crossSellingRepository;
    private SystemConfigService $systemConfig;

    public function __construct(
        EntityRepository $crossSellingRepository,
        SystemConfigService $systemConfig
    ) {
        $this->crossSellingRepository = $crossSellingRepository;
        $this->systemConfig = $systemConfig;
    }

    /**
     * Decide which cross-selling group to use for a product
     */
    public function getCrossSellingGroup(ProductEntity $product, SalesChannelContext $context): ?ProductCrossSellingEntity
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('productId', $product->getId()))
            ->addAssociation('assignedProducts')
            ->addAssociation('assignedProducts.product.cover');

        $crossSellingsResult = $this->crossSellingRepository->search($criteria, $context->getContext());


        if ($crossSellingsResult->count() === 0) {
            return null;
        }

        // Convert to EntityCollection for sorting/filtering
        $crossSellings = $crossSellingsResult->getEntities();

        // 1. Product custom field index
        $customFields = $product->getCustomFields() ?? [];
        $customIndex = $customFields['cross_selling_index'] ?? null;

        if ($customIndex !== null) {
            $match = $crossSellings->filter(
                fn (ProductCrossSellingEntity $c) => $c->getPosition() === (int) $customIndex
            )->first();

            if ($match) {
                return $match;
            }
        }

        // 2. Plugin config default index
        $defaultIndex = $this->systemConfig->get(
            'AltOffCanvas.config.defaultCrossSellingIndex',
            $context->getSalesChannelId()
        );

        if ($defaultIndex !== null) {
            $match = $crossSellings->filter(
                fn (ProductCrossSellingEntity $c) => $c->getPosition() === (int) $defaultIndex
            )->first();

            if ($match) {
                return $match;
            }
        }

        // 3. Debug logging of groups and products
        foreach ($crossSellings as $crossSelling) {
            error_log("CrossSelling name: " . $crossSelling->getName());
            foreach ($crossSelling->getAssignedProducts() ?? [] as $assignedEntity) {
                $assignedProduct = $assignedEntity->getProduct();
                if ($assignedProduct instanceof ProductEntity) {
                    error_log(" → Assigned product: " . $assignedProduct->getName() . " (ID: " . $assignedProduct->getId() . ")");
                }
            }
        }

        // 4. Fallback: first by lowest position
        $crossSellings->sort(
            fn (ProductCrossSellingEntity $a, ProductCrossSellingEntity $b) => $a->getPosition() <=> $b->getPosition()
        );

        return $crossSellings->first() ?: null;
    }
}
