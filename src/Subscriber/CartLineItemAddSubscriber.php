<?php declare(strict_types=1);

namespace AltOffCanvas\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CartLineItemAddSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        try {
            $request = $event->getRequest();
            
            // Check if this is a POST request to add line item
            if ($request->isMethod('POST') && $request->getPathInfo() === '/checkout/line-item/add') {
                error_log('=== Caught POST /checkout/line-item/add ===');
                
                // Get the product ID from lineItems data
                if ($request->request->has('lineItems')) {
                    $lineItems = $request->request->all('lineItems');
                    if (!empty($lineItems)) {
                        $firstLineItem = reset($lineItems);
                        $productId = $firstLineItem['id'] ?? null;
                        
                        error_log('Found product ID in POST data: ' . $productId);
                        
                        if ($productId) {
                            // Store in session
                            $session = $request->getSession();
                            if ($session && $session->isStarted()) {
                                $session->set('last_added_product', $productId);
                                error_log('Successfully stored last_added_product in session: ' . $productId);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('Error in onKernelRequest: ' . $e->getMessage());
        }
    }
}