document.addEventListener('DOMContentLoaded', function () {
  const root = document.body;

  const handleAction = async (courseId, action) => {
    const method = action === 'enroll' ? 'POST' : 'DELETE';
    try {
      const resp = await fetch(`/courses/${courseId}/${action}`, { method, headers: { 'Content-Type': 'application/json' } });
      const result = await resp.json();
      if (result.success) {
        alert(result.message);
        window.location.reload();
      } else {
        alert('Error: ' + (result.message || 'Operation failed'));
      }
    } catch (e) {
      console.error(e);
      alert('Network error while performing action');
    }
  };

  root.addEventListener('click', function (event) {
    const enrollBtn = event.target.closest('.btn-enroll');
    if (enrollBtn) {
      const id = enrollBtn.dataset.courseId;
      if (!id) return;
      if (confirm('Enroll in this course?')) handleAction(id, 'enroll');
      return;
    }
    const unenrollBtn = event.target.closest('.btn-unenroll');
    if (unenrollBtn) {
      const id = unenrollBtn.dataset.courseId;
      if (!id) return;
      if (confirm('Unenroll from this course?')) handleAction(id, 'unenroll');
      return;
    }
  });
});
