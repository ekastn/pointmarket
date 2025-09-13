document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('lessonModal');
  if (!modalEl) return; // Only on course page and admin
  const modal = new bootstrap.Modal(modalEl);

  const btnAdd = document.getElementById('btn-add-lesson');
  const form = document.getElementById('lesson-form');
  const saveBtn = document.getElementById('save-lesson-btn');

  const idInput = document.getElementById('lesson_id');
  const courseIdInput = document.getElementById('course_id');
  const titleInput = document.getElementById('lesson_title');
  const ordinalInput = document.getElementById('lesson_ordinal');
  const contentInput = document.getElementById('lesson_content');

  const apiBase = (typeof API_BASE_URL !== 'undefined') ? API_BASE_URL : '';
  const jwt = (typeof JWT_TOKEN !== 'undefined') ? JWT_TOKEN : '';

  const authHeaders = () => ({
    'Content-Type': 'application/json',
    ...(jwt ? { 'Authorization': `Bearer ${jwt}` } : {})
  });

  const resetForm = () => {
    idInput.value = '';
    titleInput.value = '';
    ordinalInput.value = '';
    contentInput.value = '{}';
  };

  const setModalTitle = (text) => {
    const label = document.getElementById('lessonModalLabel');
    if (label) label.textContent = text;
  };

  // Add new lesson
  if (btnAdd) {
    btnAdd.addEventListener('click', () => {
      resetForm();
      setModalTitle('Tambah Materi');
      modal.show();
    });
  }

  // Save (create or update)
  if (saveBtn) {
    saveBtn.addEventListener('click', async () => {
      const lessonId = idInput.value.trim();
      const courseId = parseInt(courseIdInput.value, 10);
      const title = titleInput.value.trim();
      const ordinal = parseInt(ordinalInput.value, 10);
      const contentRaw = contentInput.value.trim() || '{}';

      if (!title || !ordinal || !courseId) {
        alert('Mohon lengkapi judul dan urutan.');
        return;
      }

      let contentJson;
      try {
        contentJson = JSON.parse(contentRaw);
      } catch (e) {
        alert('Konten harus berupa JSON yang valid.');
        return;
      }

      try {
        if (lessonId) {
          // Update
          const resp = await fetch(`${apiBase}/api/v1/lessons/${lessonId}`, {
            method: 'PUT',
            headers: authHeaders(),
            body: JSON.stringify({ title, ordinal, content: contentJson })
          });
          const result = await resp.json();
          if (result.success) {
            alert('Materi diperbarui.');
            window.location.reload();
          } else {
            alert(result.message || 'Gagal memperbarui materi.');
          }
        } else {
          // Create
          const resp = await fetch(`${apiBase}/api/v1/lessons`, {
            method: 'POST',
            headers: authHeaders(),
            body: JSON.stringify({ course_id: courseId, title, ordinal, content: contentJson })
          });
          const result = await resp.json();
          if (result.success) {
            alert('Materi ditambahkan.');
            window.location.reload();
          } else {
            alert(result.message || 'Gagal menambahkan materi.');
          }
        }
      } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan jaringan.');
      }
    });
  }

  // Edit/Delete actions on list
  document.body.addEventListener('click', async (e) => {
    const editBtn = e.target.closest('.btn-edit-lesson');
    if (editBtn) {
      const id = editBtn.dataset.lessonId;
      const title = editBtn.dataset.lessonTitle || '';
      const ordinal = editBtn.dataset.lessonOrdinal || '';
      const content = editBtn.dataset.lessonContent || '{}';
      idInput.value = id;
      titleInput.value = title;
      ordinalInput.value = ordinal;
      try {
        const parsed = JSON.parse(content);
        contentInput.value = JSON.stringify(parsed, null, 2);
      } catch { contentInput.value = content; }
      setModalTitle('Edit Materi');
      modal.show();
      return;
    }

    const delBtn = e.target.closest('.btn-delete-lesson');
    if (delBtn) {
      const id = delBtn.dataset.lessonId;
      if (!id) return;
      if (!confirm('Hapus materi ini?')) return;
      try {
        const resp = await fetch(`${apiBase}/api/v1/lessons/${id}`, {
          method: 'DELETE',
          headers: authHeaders()
        });
        const result = await resp.json();
        if (result.success) {
          alert('Materi dihapus.');
          window.location.reload();
        } else {
          alert(result.message || 'Gagal menghapus materi.');
        }
      } catch (e2) {
        console.error(e2);
        alert('Terjadi kesalahan jaringan.');
      }
    }
  });
});

