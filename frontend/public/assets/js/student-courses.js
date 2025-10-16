document.addEventListener('DOMContentLoaded', function () {
    const root = document.body;

    // Pending action state
    let pending = { id: null, action: null };

    // Lazy-create a modal when needed (no global markup required)
    let modalEl = null;
    let modalInstance = null;
    let modalTitle = null;
    let modalBody = null;
    let modalConfirmBtn = null;

    const ensureModal = () => {
        if (modalEl || typeof bootstrap === 'undefined') return;
        modalEl = document.createElement('div');
        modalEl.className = 'modal fade';
        modalEl.tabIndex = -1;
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title pm-confirm-title">Konfirmasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pm-confirm-body">Apakah Anda yakin?</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary pm-confirm-btn">Lanjutkan</button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modalEl);
        modalInstance = new bootstrap.Modal(modalEl);
        modalTitle = modalEl.querySelector('.pm-confirm-title');
        modalBody = modalEl.querySelector('.pm-confirm-body');
        modalConfirmBtn = modalEl.querySelector('.pm-confirm-btn');
        // Bind once
        modalConfirmBtn.addEventListener('click', function () {
            if (!pending.id || !pending.action) return;
            submitActionForm(pending.id, pending.action);
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            pending = { id: null, action: null };
        });
    };

    const submitActionForm = (courseId, action) => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/courses/${courseId}/${action}`;
        if (action === 'unenroll') {
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
        }
        document.body.appendChild(form);
        form.submit();
    };

    const openConfirm = (courseId, action) => {
        pending = { id: courseId, action };
        if (typeof bootstrap === 'undefined') {
            // Fallback if Bootstrap is unavailable
            const ok = action === 'enroll'
                ? window.confirm('Enroll in this course?')
                : window.confirm('Unenroll from this course?');
            if (ok) submitActionForm(courseId, action);
            return;
        }
        ensureModal();
        if (modalTitle) modalTitle.textContent = action === 'enroll' ? 'Konfirmasi Pendaftaran' : 'Konfirmasi Pembatalan';
        if (modalBody) modalBody.textContent = action === 'enroll' ? 'Apakah Anda yakin ingin mendaftar ke kelas ini?' : 'Apakah Anda yakin ingin membatalkan kelas ini?';
        if (modalConfirmBtn) modalConfirmBtn.textContent = action === 'enroll' ? 'Daftar' : 'Batalkan';
        modalInstance.show();
    };

    root.addEventListener('click', function (event) {
        const enrollBtn = event.target.closest('.btn-enroll');
        if (enrollBtn) {
            const id = enrollBtn.dataset.courseId;
            if (!id) return;
            openConfirm(id, 'enroll');
            return;
        }
        const unenrollBtn = event.target.closest('.btn-unenroll');
        if (unenrollBtn) {
            const id = unenrollBtn.dataset.courseId;
            if (!id) return;
            openConfirm(id, 'unenroll');
            return;
        }
    });
});
