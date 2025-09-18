document.addEventListener('DOMContentLoaded', function () {
    // Delegate clicks at the document level so dynamically rendered cards also work
    document.addEventListener('click', async function (event) {
        const purchaseButton = event.target.closest('.btn-purchase');
        if (!purchaseButton) return;

        const productId = purchaseButton.dataset.productId;
        if (!productId) return;

        if (!confirm('Are you sure you want to purchase this product?')) {
            return;
        }

        try {
            const response = await fetch(`/products/${productId}/purchase`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_id: productId }),
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message || 'Purchased successfully');
                window.location.reload();
            } else {
                alert('Error: ' + (result.message || 'Failed to purchase product'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while purchasing the product.');
        }
    });

    const categoryFilter = document.querySelector('select[name="category_id"]');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function () {
            const selectedCategoryId = this.value;
            const currentUrl = new URL(window.location.href);
            if (selectedCategoryId) {
                currentUrl.searchParams.set('category_id', selectedCategoryId);
            } else {
                currentUrl.searchParams.delete('category_id');
            }
            window.location.href = currentUrl.toString();
        });
    }
});
