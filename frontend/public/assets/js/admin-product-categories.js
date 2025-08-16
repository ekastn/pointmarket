document.addEventListener('DOMContentLoaded', function () {
    const modalTambahCategory = document.getElementById('modalTambahCategory');
    const modalEditCategory = document.getElementById('modalEditCategory');
    const editCategoryForm = document.getElementById('edit-category-form');
    const categoryTable = document.querySelector('.table-responsive');

    // Handle Edit button click to populate modal
    if (categoryTable) {
        categoryTable.addEventListener('click', function (event) {
            const editButton = event.target.closest('.btn-edit-category');
            if (editButton) {
                const categoryId = editButton.dataset.categoryId;
                const categoryName = editButton.dataset.categoryName;
                const categoryDescription = editButton.dataset.categoryDescription;

                document.getElementById('edit-category-id').value = categoryId;
                document.getElementById('edit-name').value = categoryName;
                document.getElementById('edit-description').value = categoryDescription;

                // Set form action for PUT request
                editCategoryForm.action = `/product-categories/${categoryId}`;
            }
        });
    }

    // Handle Edit Category form submission (AJAX PUT request)
    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const categoryId = document.getElementById('edit-category-id').value;
            const formData = new FormData(this);
            const jsonData = {};

            for (let [key, value] of formData.entries()) {
                if (key !== '_method' && key !== 'id') {
                    jsonData[key] = value;
                }
            }

            try {
                const response = await fetch(`/product-categories/${categoryId}`, {
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
                alert('An error occurred while updating the category.');
            }
        });
    }

    // Handle Delete Category button click (AJAX DELETE request)
    if (categoryTable) {
        categoryTable.addEventListener('click', async function (event) {
            const deleteButton = event.target.closest('.btn-delete-category');
            if (deleteButton) {
                const categoryId = deleteButton.dataset.categoryId;
                if (confirm('Are you sure you want to delete this category?')) {
                    try {
                        const response = await fetch(`/product-categories/${categoryId}`, {
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
                        alert('An error occurred while deleting the category.');
                    }
                }
            }
        });
    }
});
