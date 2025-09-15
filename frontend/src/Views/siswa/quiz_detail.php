<?php
session_start();
$quiz = $data['quiz'] ?? [];
$questions = $data['questions'] ?? [];
$user = $data['user'] ?? null;
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-question-circle',
  'title' => 'Detail Kuis',
]);
?>

<div class="container pm-section">
  <div class="row">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-body">
          <h4 class="mb-1"><?php echo htmlspecialchars($quiz['title'] ?? ''); ?></h4>
          <?php if (!empty($quiz['description'])): ?>
            <p class="text-muted"><?php echo htmlspecialchars($quiz['description']); ?></p>
          <?php endif; ?>
          <div class="d-flex gap-4 small text-muted">
            <div><i class="fas fa-medal me-1"></i>Poin: <strong><?php echo htmlspecialchars($quiz['reward_points'] ?? 0); ?></strong></div>
            <div><i class="fas fa-clock me-1"></i>Durasi: <strong><?php echo isset($quiz['duration_minutes']) ? htmlspecialchars($quiz['duration_minutes']) . ' menit' : 'N/A'; ?></strong></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="fas fa-list-ol me-2"></i>Pertanyaan</span>
          <?php if (($user['role'] ?? '') === 'guru'): ?>
            <button class="btn btn-sm btn-primary" onclick="openQuestionModal()"><i class="fas fa-plus me-1"></i> Tambah Pertanyaan</button>
          <?php endif; ?>
        </div>
        <div class="card-body" id="questionsContainer">
          <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $idx => $q): ?>
              <div class="question-item border rounded p-3 mb-3" data-question-id="<?php echo (int)$q['id']; ?>" data-index="<?php echo (int)$idx; ?>" style="display: <?php echo $idx === 0 ? 'block' : 'none'; ?>;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="small text-muted">Soal <span class="q-number"><?php echo (int)($idx+1); ?></span> dari <?php echo count($questions); ?> • <?php echo htmlspecialchars($q['question_type'] ?? ''); ?></div>
                  <?php if (($user['role'] ?? '') === 'guru'): ?>
                    <div>
                      <button class="btn btn-sm btn-outline-secondary me-1" onclick="moveQuestion(<?php echo (int)$quiz['id']; ?>, <?php echo (int)$q['id']; ?>, -1)"><i class="fas fa-arrow-up"></i></button>
                      <button class="btn btn-sm btn-outline-secondary me-1" onclick="moveQuestion(<?php echo (int)$quiz['id']; ?>, <?php echo (int)$q['id']; ?>, 1)"><i class="fas fa-arrow-down"></i></button>
                      <button class="btn btn-sm btn-outline-primary me-1" onclick='openQuestionModal(<?php echo json_encode($q); ?>)'><i class="fas fa-edit"></i></button>
                      <button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(<?php echo (int)$quiz['id']; ?>, <?php echo (int)$q['id']; ?>)"><i class="fas fa-trash"></i></button>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="fw-bold mb-2"><?php echo htmlspecialchars($q['question_text'] ?? ''); ?></div>
                <?php if (($user['role'] ?? '') !== 'guru'): ?>
                  <div>
                    <?php $opts = $q['answer_options'] ?? []; if (is_array($opts)): ?>
                      <?php foreach ($opts as $key => $val): ?>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="answer_<?php echo (int)$q['id']; ?>" id="opt_<?php echo (int)$q['id']; ?>_<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($key); ?>">
                          <label class="form-check-label" for="opt_<?php echo (int)$q['id']; ?>_<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($key); ?>. <?php echo htmlspecialchars($val); ?></label>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="text-muted small">Opsi jawaban tidak tersedia.</div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
            <?php if (($user['role'] ?? '') !== 'guru'): ?>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <button class="btn btn-outline-secondary" onclick="prevQuestion()"><i class="fas fa-chevron-left"></i> Sebelumnya</button>
                <div id="progressIndicator" class="small text-muted"></div>
                <button class="btn btn-outline-secondary" onclick="nextQuestion()">Berikutnya <i class="fas fa-chevron-right"></i></button>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="text-muted">Belum ada pertanyaan.</div>
          <?php endif; ?>
        </div>
      </div>

      <?php if (($user['role'] ?? '') !== 'guru'): ?>
      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary" onclick="startQuiz(<?php echo (int)$quiz['id']; ?>)"><i class="fas fa-play me-1"></i> Mulai</button>
        <button class="btn btn-success" onclick="submitQuiz(<?php echo (int)$quiz['id']; ?>)"><i class="fas fa-check me-1"></i> Submit</button>
      </div>
      <?php endif; ?>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="small text-muted mb-1">Status</div>
          <div class="fw-bold" id="quizStatus">—</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-question me-2"></i><span id="qmTitle">Pertanyaan</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="questionForm">
          <input type="hidden" id="qId" value="">
          <div class="mb-2">
            <label class="form-label">Teks Pertanyaan</label>
            <textarea class="form-control" id="qText" rows="3" required></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Tipe</label>
            <select class="form-select" id="qType">
              <option value="multiple_choice">Multiple Choice</option>
              <option value="short">Short</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Opsi Jawaban (JSON)</label>
            <textarea class="form-control" id="qOptions" rows="3" placeholder='{"a":"...","b":"..."}'></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Jawaban Benar (opsional)</label>
            <input class="form-control" id="qCorrect" placeholder="a">
          </div>
          <div class="mb-2">
            <label class="form-label">Ordinal (opsional)</label>
            <input type="number" class="form-control" id="qOrdinal" min="1">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button class="btn btn-primary" onclick="saveQuestion(<?php echo (int)($quiz['id'] ?? 0); ?>)">Simpan</button>
      </div>
    </div>
  </div>
 </div>

<script>
const QUIZ_ID = <?php echo (int)($quiz['id'] ?? 0); ?>;
const DURATION_MIN = <?php echo isset($quiz['duration_minutes']) ? (int)$quiz['duration_minutes'] : 0; ?>;
let currentIndex = 0;
let totalQuestions = <?php echo count($questions); ?>;
function showQuestion(idx){
  const items = document.querySelectorAll('.question-item');
  if (idx < 0 || idx >= items.length) return;
  items.forEach((el,i)=> el.style.display = (i===idx?'block':'none'));
  currentIndex = idx;
  updateProgress();
}
function nextQuestion(){ showQuestion(currentIndex+1); }
function prevQuestion(){ showQuestion(currentIndex-1); }
function updateProgress(){
  const indicator = document.getElementById('progressIndicator');
  if (!indicator) return;
  const answered = Array.from(document.querySelectorAll('.question-item')).filter(q => {
    const qid = q.getAttribute('data-question-id');
    return !!document.querySelector('input[name="answer_'+qid+'"]:checked');
  }).length;
  indicator.textContent = 'Terjawab: ' + answered + ' / ' + totalQuestions;
}
document.addEventListener('change', (e)=>{ if(e.target.matches('.form-check-input')) updateProgress(); });
if (totalQuestions>0) showQuestion(0);

// Soft timer (optional)
if (DURATION_MIN > 0) {
  const end = Date.now() + DURATION_MIN*60*1000;
  const statusEl = document.getElementById('quizStatus');
  const t = setInterval(()=>{
    const rem = Math.max(0, end - Date.now());
    const mm = Math.floor(rem/60000), ss = Math.floor((rem%60000)/1000);
    statusEl.textContent = 'Sisa waktu: ' + String(mm).padStart(2,'0') + ':' + String(ss).padStart(2,'0');
    if (rem<=0) { clearInterval(t); alert('Waktu habis. Silakan submit.'); }
  }, 1000);
}

function startQuiz(id){
  fetch(API_BASE_URL + '/api/v1/quizzes/' + id + '/start', { method:'POST', headers:{'Authorization':'Bearer ' + JWT_TOKEN}})
    .then(r=>r.json()).then(d=>{ if(d.success){ alert('Kuis dimulai'); } else { alert(d.message||'Gagal'); } });
}
function submitQuiz(id){
  // Build summary
  const items = document.querySelectorAll('.question-item');
  let answered = 0; items.forEach(q=>{ const qid=q.getAttribute('data-question-id'); if(document.querySelector('input[name="answer_'+qid+'"]:checked')) answered++; });
  if(!confirm('Yakin submit kuis?\nTerjawab: ' + answered + ' / ' + items.length)) return;
  fetch(API_BASE_URL + '/api/v1/quizzes/' + id + '/submit', { method:'POST', headers:{'Content-Type':'application/json','Authorization':'Bearer ' + JWT_TOKEN}, body: JSON.stringify({}) })
    .then(r=>r.json()).then(d=>{ if(d.success){ alert('Berhasil submit'); location.reload(); } else { alert(d.message||'Gagal'); } });
}

function openQuestionModal(q){
  document.getElementById('qmTitle').textContent = q ? 'Edit Pertanyaan' : 'Tambah Pertanyaan';
  document.getElementById('qId').value = q?.id || '';
  document.getElementById('qText').value = q?.question_text || '';
  document.getElementById('qType').value = q?.question_type || 'multiple_choice';
  document.getElementById('qOptions').value = q?.answer_options ? JSON.stringify(q.answer_options) : '';
  document.getElementById('qCorrect').value = q?.correct_answer || '';
  document.getElementById('qOrdinal').value = q?.ordinal || '';
  new bootstrap.Modal(document.getElementById('questionModal')).show();
}

async function saveQuestion(quizId){
  const id = document.getElementById('qId').value;
  const payload = {
    question_text: document.getElementById('qText').value.trim(),
    question_type: document.getElementById('qType').value,
  };
  const opts = document.getElementById('qOptions').value.trim();
  if (opts) { try { payload.answer_options = JSON.parse(opts); } catch(e){ alert('Opsi jawaban harus JSON'); return; } }
  const corr = document.getElementById('qCorrect').value.trim();
  if (corr) payload.correct_answer = corr;
  const ord = document.getElementById('qOrdinal').value.trim();
  if (ord) payload.ordinal = parseInt(ord, 10);

  const url = API_BASE_URL + '/api/v1/quizzes/' + quizId + '/questions' + (id ? '/' + id : '');
  const method = id ? 'PUT' : 'POST';
  const res = await fetch(url, { method, headers:{'Content-Type':'application/json','Authorization':'Bearer ' + JWT_TOKEN}, body: JSON.stringify(payload) });
  const data = await res.json();
  if (data.success) {
    location.reload();
  } else {
    if (res.status === 409) alert('Ordinal bentrok. Coba ubah angka.'); else alert(data.message || 'Gagal menyimpan pertanyaan');
  }
}

async function deleteQuestion(quizId, questionId){
  if (!confirm('Hapus pertanyaan ini?')) return;
  const res = await fetch(API_BASE_URL + '/api/v1/quizzes/' + quizId + '/questions/' + questionId, { method:'DELETE', headers:{'Authorization':'Bearer ' + JWT_TOKEN}});
  const data = await res.json();
  if (data.success) location.reload(); else alert(data.message || 'Gagal menghapus');
}

async function moveQuestion(quizId, questionId, delta){
  // naive: fetch current ordinal from DOM and adjust
  const row = document.querySelector('[data-question-id="' + questionId + '"]');
  if (!row) return;
  const ordinalText = row.querySelector('.small').textContent.match(/#([0-9]+)/);
  let ord = ordinalText ? parseInt(ordinalText[1], 10) : 0;
  if (!ord) return;
  ord = ord + (delta > 0 ? 1 : -1);
  if (ord < 1) return;
  const payload = { ordinal: ord };
  const res = await fetch(API_BASE_URL + '/api/v1/quizzes/' + quizId + '/questions/' + questionId, { method:'PUT', headers:{'Content-Type':'application/json','Authorization':'Bearer ' + JWT_TOKEN}, body: JSON.stringify(payload)});
  const data = await res.json();
  if (data.success) location.reload(); else alert((res.status===409?'Ordinal bentrok. ':'') + (data.message||'Gagal mengubah urutan'));
}
</script>
