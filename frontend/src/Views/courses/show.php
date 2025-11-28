<?php /** @var array $course */ ?>
<div class="container-fluid">
  <?php 
    $renderer->includePartial('components/partials/page_title', [
      'icon' => 'fas fa-book-open',
      'title' => htmlspecialchars($course['title'] ?? 'Course'),
      'right' => ''
    ]);

  ?>

  <div class="row">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-2">Deskripsi</h5>
          <p class="card-text"><?= htmlspecialchars($course['description'] ?? '-') ?></p>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title mb-0">Lessons</h5>
            <?php if (!empty($user) && (($user['role'] ?? '') === 'admin' || (($user['role'] ?? '') === 'guru' && (int)($user['id'] ?? 0) === (int)($course['owner_id'] ?? -1)))): ?>
              <button class="btn btn-sm btn-success" id="btn-add-lesson" data-course-id="<?= (int)($course['id'] ?? 0) ?>">
                <i class="fas fa-plus"></i> Tambah Materi
              </button>
            <?php endif; ?>
          </div>
          <?php if (!empty($lessons)): ?>
            <ol class="mb-0">
              <?php foreach ($lessons as $lesson): ?>
                <li class="mb-2">
                  <strong><?= htmlspecialchars($lesson['title']) ?></strong>
                  <div class="text-muted small">Urutan: <?= (int)$lesson['ordinal'] ?></div>
                  <?php if (!empty($user) && (($user['role'] ?? '') === 'admin' || (($user['role'] ?? '') === 'guru' && (int)($user['id'] ?? 0) === (int)($course['owner_id'] ?? -1)))): ?>
                    <div class="mt-1">
                      <button class="btn btn-sm btn-outline-primary btn-edit-lesson"
                              data-lesson-id="<?= (int)$lesson['id'] ?>"
                              data-lesson-title="<?= htmlspecialchars($lesson['title']) ?>"
                              data-lesson-ordinal="<?= (int)$lesson['ordinal'] ?>"
                              data-lesson-content='<?= htmlspecialchars(json_encode($lesson['content'] ?? new stdClass())) ?>'>
                        Edit
                      </button>
                      <button class="btn btn-sm btn-outline-danger btn-delete-lesson" data-lesson-id="<?= (int)$lesson['id'] ?>">Hapus</button>
                    </div>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ol>
          <?php else: ?>
            <div class="text-muted">Belum ada materi.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-2">Info</h5>
          <dl class="row mb-0 small">
            <dt class="col-sm-3">Slug</dt><dd class="col-sm-9"><?= htmlspecialchars($course['slug'] ?? '') ?></dd>
            <?php 
              $guruNameRaw = $course['owner_display_name'] ?? '-';
              $guruNameTitled = $guruNameRaw && $guruNameRaw !== '-' ? ucwords(strtolower($guruNameRaw)) : '-';
            ?>
            <dt class="col-sm-3">Guru</dt><dd class="col-sm-9"><?= htmlspecialchars($guruNameTitled) ?></dd>
            <dt class="col-sm-3">Dibuat</dt><dd class="col-sm-9"><?= isset($course['created_at']) ? date('d-m-Y H:i', strtotime($course['created_at'])) : '-' ?></dd>
            <dt class="col-sm-3">Diubah</dt><dd class="col-sm-9"><?= isset($course['updated_at']) ? date('d-m-Y H:i', strtotime($course['updated_at'])) : '-' ?></dd>
          </dl>
        </div>
      </div>

      <?php if (!empty($user) && ($user['role'] ?? '') === 'guru' && !empty($course['enrolled_students'])): ?>
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-2">Daftar Siswa Terdaftar</h5>
          <?php if (!empty($course['enrolled_students'])): ?>
            <table class="table table-sm table-striped">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>NIM</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($course['enrolled_students'] as $student): ?>
                  <tr>
                    <td><?= htmlspecialchars($student['display_name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-muted">Belum ada siswa terdaftar.</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-body">
          <?php $role = $user['role'] ?? ''; ?>
          <?php if ($role === 'siswa'): ?>
            <div class="d-grid gap-2">
              <?php if (!empty($isEnrolled)): ?>
                <button type="button" class="btn btn-outline-danger btn-unenroll" data-course-id="<?= (int)($course['id'] ?? 0) ?>">Batalkan</button>
              <?php else: ?>
                <button type="button" class="btn btn-primary btn-enroll" data-course-id="<?= (int)($course['id'] ?? 0) ?>">Daftar</button>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <?php $isOwner = (($role === 'admin') || ($role === 'guru' && (int)($user['id'] ?? 0) === (int)($course['owner_id'] ?? -1))); ?>
            <?php if ($isOwner): ?>
              <div class="d-grid gap-2">
                <button class="btn btn-primary btn-edit-course"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditCourse"
                    data-course-id="<?= (int)($course['id'] ?? 0) ?>"
                    data-course-slug="<?= htmlspecialchars($course['slug'] ?? '') ?>"
                    data-course-title="<?= htmlspecialchars($course['title'] ?? '') ?>"
                    data-course-description="<?= htmlspecialchars($course['description'] ?? '') ?>"
                    data-course-metadata='<?= json_encode($course['metadata'] ?? []) ?>'>
                  Edit Kelas
                </button>

                <!-- <a href="/courses/<?= (int)($course['id'] ?? 0) ?>/edit" class="btn btn-primary">Edit Kelas</a> -->
                <button type="button" class="btn btn-success" id="btn-add-lesson-side" data-course-id="<?= (int)($course['id'] ?? 0) ?>">Tambah Materi</button>
              </div>
              <small class="text-muted d-block mt-2">Kelola materi: tambah, ubah, atau hapus di bagian Lessons.</small>
              <script>
                document.addEventListener('DOMContentLoaded', function(){
                  var sideBtn = document.getElementById('btn-add-lesson-side');
                  var trigger = function(){ var btn = document.getElementById('btn-add-lesson'); if(btn){ btn.click(); } };
                  if (sideBtn) sideBtn.addEventListener('click', trigger);
                });
              </script>
            <?php else: ?>
              <div class="text-muted small">Gunakan bagian "Lessons" untuk melihat materi.
              <?php if ($role === 'guru'): ?>
                (Anda bukan pemilik kelas ini)
              <?php endif; ?></div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($user) && (($user['role'] ?? '') === 'admin' || (($user['role'] ?? '') === 'guru' && (int)($user['id'] ?? 0) === (int)($course['owner_id'] ?? -1)))): ?>
<!-- Lesson Modal -->
<div class="modal fade" id="lessonModal" tabindex="-1" aria-labelledby="lessonModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lessonModalLabel">Tambah Materi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="lesson-form">
          <input type="hidden" id="lesson_id" name="lesson_id" value="">
          <input type="hidden" id="course_id" name="course_id" value="<?= (int)($course['id'] ?? 0) ?>">
          <div class="mb-3">
            <label for="lesson_title" class="form-label">Judul</label>
            <input type="text" class="form-control" id="lesson_title" name="title" required>
          </div>
          <div class="mb-3">
            <label for="lesson_ordinal" class="form-label">Urutan</label>
            <input type="number" class="form-control" id="lesson_ordinal" name="ordinal" min="1" required>
          </div>
          <div class="mb-3">
            <label for="lesson_content" class="form-label">Konten (JSON)</label>
            <textarea class="form-control" id="lesson_content" name="content" rows="5">{}</textarea>
            <div class="form-text">Masukkan JSON yang valid. Contoh: {"blocks":[]}</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="save-lesson-btn">Simpan</button>
      </div>
    </div>
  </div>
  </div>
<?php endif; ?>

<!-- Data Modal Box Edit Course -->
<div class="modal fade" id="modalEditCourse" tabindex="-1" aria-labelledby="modalEditCourseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditCourseLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="edit-course-form">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" id="edit-course-id">
                    <div class="mb-3">
                        <label for="edit-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit-title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="edit-slug" name="slug" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-metadata" class="form-label">Metadata (JSON)</label>
                        <textarea class="form-control" id="edit-metadata" name="metadata" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const modalEditCourse = document.getElementById('modalEditCourse');
    const editCourseForm = document.getElementById('edit-course-form');

    const editButton = document.querySelector('.btn-edit-course');

    editButton.addEventListener('click', function () {
        const courseId = editButton.dataset.courseId;
        const courseTitle = editButton.dataset.courseTitle;
        const courseSlug = editButton.dataset.courseSlug;
        const courseDescription = editButton.dataset.courseDescription;
        const courseMetadata = editButton.dataset.courseMetadata;

        document.getElementById('edit-course-id').value = courseId;
        document.getElementById('edit-title').value = courseTitle;
        document.getElementById('edit-slug').value = courseSlug;
        document.getElementById('edit-description').value = courseDescription;
        document.getElementById('edit-metadata').value = courseMetadata;

        // Set form action for PUT request
        editCourseForm.action = `/courses/${courseId}`;
    })


    if (editCourseForm) {
        editCourseForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const courseId = document.getElementById('edit-course-id').value;
            const formData = new FormData(this);
            const jsonData = {};

            // Convert FormData to JSON, excluding _method and id
            for (let [key, value] of formData.entries()) {
                if (key !== '_method' && key !== 'id') {
                    jsonData[key] = value;
                }
            }

            try {
                const response = await fetch(`/courses/${courseId}`, {
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
                alert('An error occurred while updating the course.');
            }
        });
    }
</script>
