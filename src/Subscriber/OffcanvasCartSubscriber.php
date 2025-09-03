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
        $page = $event->getPage();
        $cart = $page->getCart();

        if ($cart->getLineItems()->count() === 0) {
            return;
        }

        // Use last added item (or you could loop all items for multiple cross-sellings)
        $lastItem = $cart->getLineItems()->last();
        $productId = $lastItem->getReferencedId();

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
    }
}
