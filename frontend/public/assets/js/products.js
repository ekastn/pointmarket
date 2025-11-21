document.addEventListener('DOMContentLoaded', function () {
    const messageModalEl = document.getElementById('messageModal');
    const messageModal = new bootstrap.Modal(messageModalEl);
    const messageModalLabel = document.getElementById('messageModalLabel');
    const messageModalBody = document.getElementById('messageModalBody');
    const notificationFooter = document.getElementById('notificationFooter');
    const confirmationFooter = document.getElementById('confirmationFooter');
    const modalConfirmBtn = document.getElementById('modalConfirmBtn');
    const modalCancelBtn = document.getElementById('modalCancelBtn');

    // Hides modal title for a cleaner look
    if (messageModalLabel) {
        messageModalLabel.style.display = 'none';
    }

    function showMessageModal(title, message, isSuccess) {
        notificationFooter.style.display = 'block';
        confirmationFooter.style.display = 'none';

        if (messageModalLabel) {
            messageModalLabel.textContent = title;
            messageModalLabel.classList.toggle('text-danger', !isSuccess);
            messageModalLabel.classList.toggle('text-success', isSuccess);
        }
        if (messageModalBody) {
            messageModalBody.textContent = message;
        }
        messageModal.show();
    }

    function showConfirmationModal(message) {
        return new Promise((resolve, reject) => {
            notificationFooter.style.display = 'none';
            confirmationFooter.style.display = 'block';

            if (messageModalBody) {
                messageModalBody.textContent = message;
            }

            const onConfirm = () => {
                cleanup();
                resolve();
            };

            const onCancel = () => {
                cleanup();
                reject();
            };

            const cleanup = () => {
                modalConfirmBtn.removeEventListener('click', onConfirm);
                modalCancelBtn.removeEventListener('click', onCancel);
                messageModalEl.removeEventListener('hidden.bs.modal', onCancel);
            };

            modalConfirmBtn.addEventListener('click', onConfirm, { once: true });
            modalCancelBtn.addEventListener('click', onCancel, { once: true });
            messageModalEl.addEventListener('hidden.bs.modal', onCancel, { once: true });

            messageModal.show();
        });
    }

    // Delegate clicks at the document level
    document.addEventListener('click', async function (event) {
        const purchaseButton = event.target.closest('.btn-purchase');
        if (!purchaseButton) return;

        const productId = purchaseButton.dataset.productId;
        if (!productId) return;

        try {
            await showConfirmationModal('Apakah Anda yakin ingin menukar produk ini?');
        } catch {
            return; // User cancelled
        }

        try {
            const response = await fetch(`/products/${productId}/purchase`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId }),
            });

            const result = await response.json();

            if (result.success) {
                showMessageModal('Berhasil', result.message || 'Pembelian berhasil!', true);
                messageModalEl.addEventListener('hidden.bs.modal', () => window.location.reload(), { once: true });
            } else {
                const errorMap = {
                    'insufficient points': 'Poin Anda tidak mencukupi untuk menukar produk ini.',
                    'out of stock': 'Produk ini sudah habis terjual.',
                };
                const knownMessage = errorMap[result.message] || 'Terjadi kesalahan. Gagal menukar produk.';
                showMessageModal('Gagal', knownMessage, false);
            }
        } catch (error) {
            console.error('Error:', error);
            showMessageModal('Error', 'Terjadi kesalahan saat melakukan pembelian.', false);
        }
    });

    const categoryFilter = document.querySelector('select[name="category_id"]');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function () {
            const selectedCategoryId = this.value;
            const currentUrl = new URL(window.location.href);
            if (selectedCategoryId) {
                currentUrl.searchParams.set('category_id', selectedCategoryId);
            }
            else {
                currentUrl.searchParams.delete('category_id');
            }
            window.location.href = currentUrl.toString();
        });
    }
});

