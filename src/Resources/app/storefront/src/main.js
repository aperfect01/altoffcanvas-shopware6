// Track the last added product via JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Listen for form submissions on add-to-cart forms
    document.addEventListener('submit', function(event) {
        const form = event.target;
        
        // Check if this is an add-to-cart form
        if (form.matches('form[action*="line-item/add"]') || form.classList.contains('buy-widget')) {
            // Get product ID from form data
            const lineItemsInputs = form.querySelectorAll('input[name*="lineItems"][name*="[id]"]');
            if (lineItemsInputs.length > 0) {
                const productId = lineItemsInputs[0].value;
                
                // Store in localStorage for the offcanvas to read
                localStorage.setItem('lastAddedProductId', productId);
                
                console.log('Stored lastAddedProductId:', productId);
            }
        }
    });
});
