<?php /** @var array $states */ /** @var array $meta */ /** @var array $filters */ ?>
<div class="container-fluid">
  <?php $renderer->includePartial('components/partials/page_title', [ 'icon'=>'fas fa-stream', 'title'=>'Unique States', 'right'=>'' ]); ?>

  <?php if (!empty($messages['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($messages['success']) ?></div>
  <?php elseif (!empty($messages['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars(is_array($messages['error']) ? json_encode($messages['error']) : $messages['error']) ?></div>
  <?php endif; ?>

  <div class="row pm-section">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
          <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Search</label>
              <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" placeholder="State/Description contains..." />
            </div>
            <div class="col-md-3">
              <label class="form-label">Sort</label>
              <select name="sort" class="form-select">
                <?php $sort = $filters['sort'] ?? 'state asc'; ?>
                <option value="state asc" <?= ($sort==='state asc')?'selected':'' ?>>State ↑</option>
                <option value="state desc" <?= ($sort==='state desc')?'selected':'' ?>>State ↓</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Limit</label>
              <select name="limit" class="form-select">
                <?php foreach ([20,50,100] as $opt): ?>
                  <option value="<?= $opt ?>" <?= ((int)($filters['limit'] ?? 20)===$opt)?'selected':'' ?>><?= $opt ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <button class="btn btn-primary w-100" type="submit"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row pm-section">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0"><i class="fas fa-plus me-2"></i>Create State</h5>
        </div>
        <div class="card-body">
          <form method="post" action="/admin/recommendations/states">
            <div class="row g-2 align-items-end">
              <div class="col-md-6">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-control" placeholder="e.g., V_high_mslq_medium_ams_extrinsic_eng_low" required />
                <div class="form-text">Format: [V|A|R|K]_high_mslq_[low|medium|high]_ams_[amotivation|extrinsic|achievement|intrinsic]_eng_[low|medium|high]</div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" />
              </div>
              <div class="col-md-2">
                <button class="btn btn-success w-100" type="submit"><i class="fas fa-plus me-1"></i>Add</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php
    $columns = [
      ['label'=>'ID','key'=>'id'],
      ['label'=>'State','key'=>'state'],
      ['label'=>'Description','key'=>'description'],
    ];
    $actions = [
      [
        'label'=>'Edit','icon'=>'fas fa-edit','class'=>'btn-outline-primary js-edit-state',
        'attributes'=>function($row){
          if (!is_array($row)) { return []; }
          return [
            'data-id'=>(int)($row['id'] ?? 0),
            'data-state'=>(string)($row['state'] ?? ''),
            'data-description'=>(string)($row['description'] ?? ''),
          ];
        }
      ],
      [
        'label'=>'Delete','icon'=>'fas fa-trash','class'=>'btn-outline-danger',
        'attributes'=>function($row) use ($filters){
          $id = is_array($row) ? (int)($row['id'] ?? 0) : 0;
          return [
            'href'=>'#',
            'onclick'=>"if(confirm('Delete this state?')){var f=document.getElementById('del-".$id."'); if(f) f.submit();} return false;",
          ];
        }
      ],
    ];

    // Build pagination for component
    $total = (int)($meta['total'] ?? 0); $limit=(int)($meta['limit'] ?? 20); $offset=(int)($meta['offset'] ?? 0);
    $current_page = ($limit > 0) ? (int)floor($offset / $limit) + 1 : 1;
    $total_pages = ($limit > 0) ? (int)ceil($total / $limit) : 1;
    $start_record = ($total === 0) ? 0 : ($offset + 1);
    $end_record = min($offset + $limit, $total);
    $base_params = $filters;

    $renderer->includePartial('components/partials/table', [
      'columns'=>$columns,
      'actions'=>$actions,
      'data'=>$states,
      'pagination'=>[
        'current_page'=>$current_page,
        'total_pages'=>$total_pages,
        'total_records'=>$total,
        'start_record'=>$start_record,
        'end_record'=>$end_record,
        'base_params'=>$base_params,
      ],
      'empty_message'=>'No states found.',
    ]);
  ?>

  <?php foreach ($states as $st): if (is_array($st)): ?>
    <form id="del-<?= (int)($st['id']??0) ?>" method="post" action="/admin/recommendations/states/delete" style="display:none;">
      <input type="hidden" name="id" value="<?= (int)($st['id']??0) ?>" />
    </form>
  <?php endif; endforeach; ?>

  <script>
    (function(){
      // Build and attach edit modal once
      const tpl = `
<div class=\"modal fade\" id=\"editStateModal\" tabindex=\"-1\" aria-hidden=\"true\">
  <div class=\"modal-dialog\">
    <div class=\"modal-content\">
      <div class=\"modal-header\"><h5 class=\"modal-title\"><i class=\"fas fa-edit me-2\"></i>Edit State</h5><button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\"></button></div>
      <form method=\"post\" action=\"/admin/recommendations/states/update\">
        <div class=\"modal-body\">
          <input type=\"hidden\" name=\"id\" id=\"edit-id\" />
          <div class=\"mb-3\">
            <label class=\"form-label\">State</label>
            <input type=\"text\" class=\"form-control\" name=\"state\" id=\"edit-state\" required />
            <div class=\"form-text\">Changing state may impact Items. Use "Update Items" to propagate.</div>
          </div>
          <div class=\"mb-3\">
            <label class=\"form-label\">Description</label>
            <input type=\"text\" class=\"form-control\" name=\"description\" id=\"edit-description\" />
          </div>
          <div class=\"form-check\">
            <input class=\"form-check-input\" type=\"checkbox\" name=\"update_items\" value=\"1\" id=\"edit-update-items\">
            <label class=\"form-check-label\" for=\"edit-update-items\">Update Items to new state</label>
          </div>
        </div>
        <div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-outline-secondary\" data-bs-dismiss=\"modal\">Cancel</button><button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-save me-1\"></i>Save</button></div>
      </form>
    </div>
  </div>
</div>`;
      const wrap = document.createElement('div'); wrap.innerHTML = tpl; document.body.appendChild(wrap);
      let bsModal = null; const modalEl = document.getElementById('editStateModal');
      function showModal(){ if (!bsModal && window.bootstrap) bsModal = new bootstrap.Modal(modalEl); bsModal && bsModal.show(); }
      Array.from(document.querySelectorAll('.js-edit-state')).forEach(btn => {
        btn.addEventListener('click', function(){
          document.getElementById('edit-id').value = this.getAttribute('data-id');
          document.getElementById('edit-state').value = this.getAttribute('data-state') || '';
          document.getElementById('edit-description').value = this.getAttribute('data-description') || '';
          document.getElementById('edit-update-items').checked = false;
          showModal();
        });
      });
    })();
  </script>
</div>
