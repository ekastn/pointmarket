document.addEventListener('DOMContentLoaded', function () {
    const modalTambahProduct = document.getElementById('modalTambahProduct');
    const modalEditProduct = document.getElementById('modalEditProduct');
    const editProductForm = document.getElementById('edit-product-form');
    const productTable = document.querySelector('.table-responsive');

    // Handle Edit button click to populate modal
    if (productTable) {
        productTable.addEventListener('click', function (event) {
            const editButton = event.target.closest('.btn-edit-product');
            if (editButton) {
                const productId = editButton.dataset.productId;
                const productName = editButton.dataset.productName;
                const productDescription = editButton.dataset.productDescription;
                const productPointsPrice = editButton.dataset.productPointsPrice;
                const productType = editButton.dataset.productType;
                const productStockQuantity = editButton.dataset.productStockQuantity;
                const productIsActive = editButton.dataset.productIsActive;
                const productMetadata = editButton.dataset.productMetadata;

                document.getElementById('edit-product-id').value = productId;
                document.getElementById('edit-name').value = productName;
                document.getElementById('edit-description').value = productDescription;
                document.getElementById('edit-points_price').value = productPointsPrice;
                document.getElementById('edit-type').value = productType;
                document.getElementById('edit-stock_quantity').value = productStockQuantity;
                document.getElementById('edit-is_active').checked = productIsActive === '1';
                document.getElementById('edit-metadata').value = productMetadata;

                // Set form action for PUT request
                editProductForm.action = `/products/${productId}`;
            }
        });
    }

    // Handle Edit Product form submission (AJAX PUT request)
    if (editProductForm) {
        editProductForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const productId = document.getElementById('edit-product-id').value;
            const formData = new FormData(this);
            const jsonData = {};

            for (let [key, value] of formData.entries()) {
                if (key !== '_method' && key !== 'id') {
                    jsonData[key] = value;
                }
            }
            jsonData['is_active'] = document.getElementById('edit-is_active').checked;

            try {
                const response = await fetch(`/products/${productId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(jsonData),
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
                alert('An error occurred while updating the product.');
            }
        });
    }

    // Handle Delete Product button click (AJAX DELETE request)
    if (productTable) {
        productTable.addEventListener('click', async function (event) {
            const deleteButton = event.target.closest('.btn-delete-product');
            if (deleteButton) {
                const productId = deleteButton.dataset.productId;
                if (confirm('Are you sure you want to delete this product?')) {
                    try {
                        const response = await fetch(`/products/${productId}`, {
                            method: 'DELETE',
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
                        alert('An error occurred while deleting the product.');
                    }
                }
            }
        });
    }
});
