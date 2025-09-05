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
        try {
            error_log('=== OffcanvasCartSubscriber called ===');
        
        $page = $event->getPage();
        $cart = $page->getCart();

        if ($cart->getLineItems()->count() === 0) {
            return;
        }

        // Try to get the actual added product from session
        $request = $event->getRequest();
        $session = $request->getSession();
        $addedProductId = null;
        $addedProductName = null;
        
        if ($session && $session->isStarted()) {
            $addedProductId = $session->get('last_added_product');
            $addedProductName = $session->get('last_added_product_name');
            error_log('Found in session - ID: ' . $addedProductId . ', Name: ' . $addedProductName);
        }
        
        // If we have a product name, try to find it in cart by name
        if ($addedProductName) {
            foreach ($cart->getLineItems() as $lineItem) {
                if ($lineItem->getLabel() === $addedProductName) {
                    $addedProductId = $lineItem->getReferencedId();
                    error_log('Found matching product by name: ' . $addedProductName . ' -> ' . $addedProductId);
                    break;
                }
            }
        }
        
        // Fallback to last item if no session data
        if (!$addedProductId) {
            $lastItem = $cart->getLineItems()->last();
            $addedProductId = $lastItem->getReferencedId();
            error_log('Using fallback (last item): ' . $addedProductId);
        }
        
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
        } catch (\Exception $e) {
            error_log('Error in OffcanvasCartSubscriber: ' . $e->getMessage());
        }
    }
}
