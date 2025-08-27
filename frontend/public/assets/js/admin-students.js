const tableEl = document.querySelector('.table');

if (tableEl) {
  tableEl.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-edit-student');
    if (!btn) return;
    document.getElementById('edit-user-id').value = btn.dataset.userId;
    document.getElementById('edit-student-id').value = btn.dataset.studentId || '';
    document.getElementById('edit-program-id').value = btn.dataset.programId || '';
    document.getElementById('edit-cohort-year').value = btn.dataset.cohortYear || '';
    document.getElementById('edit-status').value = btn.dataset.status || 'active';
  });
}

const form = document.getElementById('edit-student-form');
if (form) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const userId = document.getElementById('edit-user-id').value;
    const payload = {
      student_id: document.getElementById('edit-student-id').value,
      program_id: parseInt(document.getElementById('edit-program-id').value, 10),
      cohort_year: document.getElementById('edit-cohort-year').value ? parseInt(document.getElementById('edit-cohort-year').value, 10) : undefined,
      status: document.getElementById('edit-status').value,
    };
    try {
      const resp = await fetch(`/students/${userId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });
      const data = await resp.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Gagal menyimpan data siswa');
      }
    } catch (err) {
      console.error(err);
      alert('Terjadi kesalahan saat menyimpan');
    }
  });
}

