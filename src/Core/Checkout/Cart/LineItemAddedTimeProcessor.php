<?php declare(strict_types=1);

namespace AltOffCanvas\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class LineItemAddedTimeProcessor implements CartProcessorInterface
{
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        foreach ($toCalculate->getLineItems()->getFlat() as $lineItem) {
            // Add timestamp if not already present
            if (!$lineItem->getPayloadValue('addedTime')) {
                $lineItem->setPayloadValue('addedTime', time());
            }
            
            // Update timestamp when quantity changes (item re-added)
            if ($original->has($lineItem->getId())) {
                $originalLineItem = $original->get($lineItem->getId());
                if ($originalLineItem && $originalLineItem->getQuantity() !== $lineItem->getQuantity()) {
                    $lineItem->setPayloadValue('addedTime', time());
                }
            }
        }
    }
}