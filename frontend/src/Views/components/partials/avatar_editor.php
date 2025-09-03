<?php
// Props: $avatar (string, required)
$avatar = $avatar ?? 'https://i.pravatar.cc/150?img=12';
?>
<style>
    /* Avatar editor component styles */
    #avatar-container {
        width: 128px;
        height: 128px;
        border-radius: 50%;
        overflow: hidden;
        position: relative;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    #avatar-container img {
        object-fit: cover;
        user-select: none;
        -webkit-user-drag: none;
    }
    #avatar-container.editing {
        border: 2px dashed var(--bs-primary);
        cursor: pointer;
    }
    #avatar-container.editing:hover {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    #avatar-overlay {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.45);
        color: #fff;
        font-size: 12px;
        text-align: center;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
        pointer-events: none;
    }
    #avatar-container.editing #avatar-overlay {
        opacity: 1;
    }
    #avatar-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
    }
    #edit-actions {
        display: inline-flex;
        gap: 0.5rem;
    }
    @media (prefers-reduced-motion: reduce) {
        #avatar-container,
        #avatar-overlay {
            transition: none;
        }
    }
</style>

<div id="avatar-container" class="position-relative mx-auto mb-3 rounded-circle">
    <img id="avatar-img" src="<?= htmlspecialchars($avatar); ?>" class="rounded-circle w-100 h-100" alt="Avatar" draggable="false">
    <div id="avatar-overlay">
        <div>
            <i class="fas fa-upload mb-1"></i>
            <div>Drag & drop</div>
            <div>or click to choose</div>
        </div>
    </div>
  </div>
  <input id="avatar-file" type="file" accept="image/png,image/jpeg,image/webp" class="d-none">

<div id="avatar-actions" class="mb-2 text-center">
    <button id="btn-edit-avatar" type="button" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-image me-1"></i>
        Edit Photo
    </button>
    <div id="edit-actions" class="d-none d-inline-flex">
        <button id="btn-save-avatar" type="button" class="btn btn-primary btn-sm">
            <i class="fas fa-save me-1"></i>
            Save Photo
        </button>
        <button id="btn-cancel-avatar" type="button" class="btn btn-light btn-sm">
            Cancel
        </button>
    </div>
</div>

<script>
    // Avatar edit logic (preview + drag&drop + basic actions)
    (function () {
        const container = document.getElementById('avatar-container');
        const img = document.getElementById('avatar-img');
        const overlay = document.getElementById('avatar-overlay');
        const fileInput = document.getElementById('avatar-file');
        const btnEdit = document.getElementById('btn-edit-avatar');
        const editActions = document.getElementById('edit-actions');
        const btnSave = document.getElementById('btn-save-avatar');
        const btnCancel = document.getElementById('btn-cancel-avatar');

        if (!container || !img || !overlay || !fileInput || !btnEdit || !editActions || !btnSave || !btnCancel) return;

        let editMode = false;
        let selectedFile = null;
        const originalSrc = img.src;

        function enterEditMode() {
            editMode = true;
            btnEdit.classList.add('d-none');
            editActions.classList.remove('d-none');
            container.classList.add('editing');
        }
        function exitEditMode() {
            editMode = false;
            btnEdit.classList.remove('d-none');
            editActions.classList.add('d-none');
            container.classList.remove('editing');
            selectedFile = null;
            img.src = originalSrc;
        }

        function handleFiles(files) {
            if (!files || !files.length) return;
            const f = files[0];
            // Basic validations
            const okTypes = ['image/png', 'image/jpeg', 'image/webp'];
            if (!okTypes.includes(f.type)) { alert('Unsupported format. Use JPG, PNG, or WEBP.'); return; }
            if (f.size > 5 * 1024 * 1024) { alert('Max file size is 5 MB.'); return; }
            selectedFile = f;
            const url = URL.createObjectURL(f);
            img.src = url;
        }

        // Events
        btnEdit.addEventListener('click', () => { enterEditMode(); });
        btnCancel.addEventListener('click', () => { exitEditMode(); });
        btnSave.addEventListener('click', () => {
            if (!selectedFile) { alert('No image selected.'); return; }
            alert('Save image (demo): ' + selectedFile.name);
            exitEditMode();
        });

        // Click to open file dialog when editing
        container.addEventListener('click', () => { if (editMode) fileInput.click(); });
        fileInput.addEventListener('change', (e) => { handleFiles(e.target.files); });

        // Drag & drop
        ['dragenter', 'dragover'].forEach((evt) => {
            container.addEventListener(evt, (e) => {
                if (!editMode) return;
                e.preventDefault();
                e.stopPropagation();
                container.classList.add('editing');
            });
        });
        ['dragleave', 'dragend', 'drop'].forEach((evt) => {
            container.addEventListener(evt, (e) => {
                if (!editMode) return;
                e.preventDefault();
                e.stopPropagation();
                // keep editing class; overlay visibility tied to edit mode
            });
        });
        container.addEventListener('drop', (e) => {
            if (!editMode) return;
            const dt = e.dataTransfer; if (!dt) return;
            handleFiles(dt.files);
        });
    })();
</script>
