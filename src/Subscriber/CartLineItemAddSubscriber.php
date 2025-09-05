<?php declare(strict_types=1);

namespace AltOffCanvas\Subscriber;

use Shopware\Core\Checkout\Cart\Event\LineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\LineItemQuantityChangedEvent;
use Shopware\Core\Framework\Event\BeforeSendResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
            LineItemAddedEvent::class => 'onLineItemAdded',
            LineItemQuantityChangedEvent::class => 'onLineItemQuantityChanged',
            BeforeSendResponseEvent::class => 'onBeforeSendResponse',
        ];
    }

    public function onLineItemAdded(LineItemAddedEvent $event): void
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                return;
            }

            $lineItem = $event->getLineItem();
            $productId = $lineItem->getReferencedId();
            
            if ($productId) {
                // Store the last added product ID in session with proper error handling
                $session = $request->getSession();
                if ($session && $session->isStarted()) {
                    $session->set('last_added_product', $productId);
                    error_log('LineItemAdded - Stored last_added_product in session: ' . $productId);
                }
            }
        } catch (\Exception $e) {
            error_log('Error in onLineItemAdded: ' . $e->getMessage());
        }
    }

    public function onLineItemQuantityChanged(LineItemQuantityChangedEvent $event): void
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                return;
            }

            $lineItem = $event->getLineItem();
            $productId = $lineItem->getReferencedId();
            
            if ($productId) {
                // Store the last modified product ID in session with proper error handling
                $session = $request->getSession();
                if ($session && $session->isStarted()) {
                    $session->set('last_added_product', $productId);
                    error_log('LineItemQuantityChanged - Stored last_added_product in session: ' . $productId);
                }
            }
        } catch (\Exception $e) {
            error_log('Error in onLineItemQuantityChanged: ' . $e->getMessage());
        }
    }

    public function onBeforeSendResponse(BeforeSendResponseEvent $event): void
    {
        try {
            $request = $event->getRequest();
            
            // Check if this is an add-to-cart request
            if ($request->request->has('lineItems') && $request->getPathInfo() === '/checkout/line-item/add') {
                $lineItems = $request->request->all('lineItems');
                if (!empty($lineItems)) {
                    $firstLineItem = reset($lineItems);
                    $productId = $firstLineItem['id'] ?? null;
                    
                    if ($productId) {
                        $session = $request->getSession();
                        if ($session && $session->isStarted()) {
                            $session->set('last_added_product', $productId);
                            error_log('BeforeSendResponse - Stored last_added_product from request: ' . $productId);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('Error in onBeforeSendResponse: ' . $e->getMessage());
        }
    }
}