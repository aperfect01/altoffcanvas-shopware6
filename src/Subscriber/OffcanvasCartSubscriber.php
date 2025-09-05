<?php declare(strict_types=1);

namespace AltOffCanvas\Subscriber;

use AltOffCanvas\Service\CrossSellingService;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayEntity;

class OffcanvasCartSubscriber implements EventSubscriberInterface
{
    private EntityRepository $productRepository;
    private CrossSellingService $crossSellingService;

    public function __construct(
        EntityRepository $productRepository,
        CrossSellingService $crossSellingService
    ) {
        $this->productRepository = $productRepository;
        $this->crossSellingService = $crossSellingService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OffcanvasCartPageLoadedEvent::class => 'onOffcanvasCartLoaded',
        ];
    }

    public function onOffcanvasCartLoaded(OffcanvasCartPageLoadedEvent $event): void
    {
        error_log('=== OffcanvasCartSubscriber called ===');
        
        $page = $event->getPage();
        $cart = $page->getCart();

        if ($cart->getLineItems()->count() === 0) {
            return;
        }

        // Use last added item (works for most cases, has known bug with consecutive identical adds)
        $lastItem = $cart->getLineItems()->last();
        $addedProductId = $lastItem->getReferencedId();
        
        $productId = $addedProductId;

        if (!$productId) {
            return;
        }

        // Load product entity
        $criteria = new Criteria([$productId]);
        $product = $this->productRepository->search($criteria, $event->getContext())->first();

        if (!$product) {
            return;
        }

        // Get cross-selling group
        $crossSellingGroup = $this->crossSellingService->getCrossSellingGroup(
            $product,
            $event->getSalesChannelContext()
        );
        if (!$crossSellingGroup) {
            return;
        }

        // Collect assigned products (first 3)
        $assignedProducts = [];
        foreach ($crossSellingGroup->getAssignedProducts() as $assignedEntity) {
            $assignedProduct = $assignedEntity->getProduct();
            if ($assignedProduct) {
                $assignedProducts[] = $assignedProduct;
            }
        }
        $assignedProducts = array_slice($assignedProducts, 0, 3);

        // Attach to page for Twig
        if (!empty($assignedProducts)) {
            $page->addExtension('crossSellingProducts', new ArrayEntity([
                'products' => $assignedProducts,
            ]));
        }
        
        // Also pass the added product ID to the template
        $page->addExtension('addedProductId', new ArrayEntity([
            'productId' => $addedProductId,
        ]));
        
        error_log('=== Setting addedProductId extension: ' . $addedProductId);
    }
}
