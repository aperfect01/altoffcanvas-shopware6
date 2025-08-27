<?php declare(strict_types=1);

namespace AltOffCanvas\Storefront\Controller;

use Shopware\Storefront\Controller\CheckoutController;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CheckoutControllerDecorator extends CheckoutController
{
    private CheckoutController $decorated;

    public function __construct(CheckoutController $decorated)
    {
        $this->decorated = $decorated;
    }

    public function cartOffcanvas(Request $request, SalesChannelContext $context): Response
    {
        // Detect if this is add-to-cart
        $isAddToCart = $this->isAddToCartRequest($request);
        
        // Call original method
        $response = $this->decorated->cartOffcanvas($request, $context);
        
        // Add our custom data
        $response->getContext()['isMinimalView'] = $isAddToCart;
        
        return $response;
    }
    
    private function isAddToCartRequest(Request $request): bool
    {
        // Logic to detect add-to-cart vs show-cart
        // Based on request parameters, headers, etc.
    }
}