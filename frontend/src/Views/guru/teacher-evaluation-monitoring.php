<?php
/**
 * @var array $dashboardData
 * @var string $title
 */
?>

<div class="container-fluid">
<?php $renderer->includePartial('components/partials/page_title', [
    'icon' => 'fas fa-chart-line',
    'title' => htmlspecialchars($title ?: 'Monitoring Evaluasi'),
]); ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Weekly Evaluation Monitoring</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Completed</th>
                                    <th>Pending</th>
                                    <th>Overdue</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($dashboardData)) : ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No data available.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($dashboardData as $data) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($data['student_name']) ?></td>
                                            <td>
                                                <span class="badge bg-success"><?= htmlspecialchars($data['completed_count']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning"><?= htmlspecialchars($data['pending_count']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger"><?= htmlspecialchars($data['overdue_count']) ?></span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary pm-view-student-chart"
                                                        data-student-id="<?= htmlspecialchars((string)($data['student_id'] ?? '')) ?>"
                                                        data-student-name="<?= htmlspecialchars($data['student_name'] ?? '') ?>">
                                                    <i class="fas fa-chart-line me-1"></i> Lihat Grafik
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Per-Student Weekly Scores Chart -->
    <div class="modal fade" id="studentChartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i>Grafik Skor Evaluasi — <span id="pmStudentName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Tarik untuk menggeser, scroll untuk zoom.</small>
                        <button type="button" id="pmResetZoom" class="btn btn-sm btn-outline-secondary">reset zoom</button>
                    </div>
                    <div class="position-relative" style="height: 320px;">
                        <canvas id="pmStudentScoresCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart libs (scoped to this page) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2"></script>
<script>
(function(){
    let modalEl = document.getElementById('studentChartModal');
    let nameEl = document.getElementById('pmStudentName');
    let canvas = document.getElementById('pmStudentScoresCanvas');
    let resetBtn = document.getElementById('pmResetZoom');
    let currentChart = null;
    if (window.Chart && window.ChartZoom) { Chart.register(window.ChartZoom); }

    function buildSeries(list) {
        const points = {};
        (list || []).forEach(function(row){
            const status = (row.status || '').toLowerCase();
            if (status !== 'completed') return;
            if (row.score === undefined || row.score === null) return;
            const dateStr = row.completed_at || row.due_date || null;
            if (!dateStr) return;
            const ts = Date.parse(dateStr);
            if (!ts) return;
            const label = new Date(ts);
            // ISO week label (approx): YYYY-WW
            const yy = label.getUTCFullYear();
            const onejan = new Date(Date.UTC(yy,0,1));
            const week = Math.ceil((((label - onejan) / 86400000) + onejan.getUTCDay()+1)/7);
            const lbl = yy + '-W' + String(week).padStart(2,'0');
            const type = String(row.questionnaire_type || '').toUpperCase();
            if (!points[lbl]) points[lbl] = { mslq: null, ams: null, ts: ts };
            if (type === 'MSLQ') points[lbl].mslq = Number(row.score);
            if (type === 'AMS') points[lbl].ams = Number(row.score);
        });
        const entries = Object.entries(points).sort((a,b)=>a[1].ts - b[1].ts);
        const labels = entries.map(e=>e[0]);
        const mslq = entries.map(e=>e[1].mslq);
        const ams  = entries.map(e=>e[1].ams);
        return { labels, mslq, ams };
    }

    function renderChart(data){
        if (currentChart) { currentChart.destroy(); currentChart = null; }
        const ctx = canvas.getContext('2d');
        currentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'MSLQ', data: data.mslq, borderColor: '#2ecc71', backgroundColor:'rgba(46,204,113,0.15)', tension:0.3, cubicInterpolationMode:'monotone', spanGaps:true, pointRadius:2, pointHoverRadius:4 },
                    { label: 'AMS',  data: data.ams,  borderColor: '#3498db', backgroundColor:'rgba(52,152,219,0.15)', tension:0.3, cubicInterpolationMode:'monotone', spanGaps:true, pointRadius:2, pointHoverRadius:4 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'nearest', intersect: false },
                scales: { y: { min:0, max:10, ticks:{ stepSize:1 } } },
                plugins: {
                    legend: { position: 'bottom' },
                    zoom: { pan:{enabled:true, mode:'xy'}, zoom:{ wheel:{enabled:true}, pinch:{enabled:true}, mode:'xy' }, limits:{ y:{min:0,max:10} } },
                    decimation: { enabled:true, algorithm:'min-max' }
                }
            }
        });
    }

    document.querySelectorAll('.pm-view-student-chart').forEach(function(btn){
        btn.addEventListener('click', function(){
            const id = this.getAttribute('data-student-id');
            const name = this.getAttribute('data-student-name') || '';
            if (nameEl) nameEl.textContent = name;
            // Show modal immediately
            const m = new bootstrap.Modal(modalEl);
            m.show();
            // Fetch data
            fetch(`/guru/weekly-evaluations/${id}/chart-data`, { credentials:'same-origin' })
                .then(r=>r.json())
                .then(json => {
                    if (!json.success) throw new Error(json.message || 'failed');
                    const shaped = buildSeries(json.data || []);
                    renderChart(shaped);
                })
                .catch(()=>{
                    // Render empty chart
                    renderChart({ labels: [], mslq: [], ams: [] });
                });
        });
    });

    if (resetBtn) resetBtn.addEventListener('click', function(){ if (currentChart && currentChart.resetZoom) currentChart.resetZoom(); });
})();
</script>
