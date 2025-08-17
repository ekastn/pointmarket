document.addEventListener('DOMContentLoaded', function () {
    const productContainer = document.querySelector('.row'); // Assuming products are in a row div

    // Handle Purchase button click (AJAX POST request)
    if (productContainer) {
        productContainer.addEventListener('click', async function (event) {
            const purchaseButton = event.target.closest('.btn-purchase');
            if (purchaseButton) {
                const productId = purchaseButton.dataset.productId;
                if (confirm('Are you sure you want to purchase this product?')) {
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
                            alert(result.message);
                            window.location.reload(); // Reload page to see changes
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while purchasing the product.');
                    }
                }
            }
        });
    }

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
