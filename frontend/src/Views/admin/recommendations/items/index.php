<?php /** @var array $items */ /** @var array $meta */ /** @var array $filters */ ?>
<div class="container-fluid">
  <?php
    $renderer->includePartial('components/partials/page_title', [
      'icon' => 'fas fa-list',
      'title' => 'Item Rekomendasi',
      'right' => ''
    ]);
  ?>

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
            <div class="col-md-3">
              <label class="form-label">State</label>
              <input type="text" name="state_like" class="form-control" value="<?= htmlspecialchars($filters['state_like'] ?? '') ?>" placeholder="contains..." />
            </div>
            <div class="col-md-2">
              <label class="form-label">Action</label>
              <select name="action_code" class="form-select">
                <option value="">Any</option>
                <?php foreach ([101=>'Reward',102=>'Produk',103=>'Hukuman',105=>'Misi',106=>'Coaching'] as $code=>$label): ?>
                  <option value="<?= $code ?>" <?= (isset($filters['action_code']) && (int)$filters['action_code']===$code)?'selected':'' ?>><?= $code ?> - <?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Ref Type</label>
              <select name="ref_type" class="form-select">
                <option value="">Any</option>
                <?php foreach (['mission','product','reward','coaching','punishment','badge'] as $rt): ?>
                  <option value="<?= $rt ?>" <?= (isset($filters['ref_type']) && $filters['ref_type']===$rt)?'selected':'' ?>><?= $rt ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Ref ID</label>
              <input type="number" name="ref_id" class="form-control" value="<?= htmlspecialchars($filters['ref_id'] ?? '') ?>" />
            </div>
            <div class="col-md-2">
              <label class="form-label">Active</label>
              <select name="active" class="form-select">
                <option value="">All</option>
                <option value="1" <?= (($filters['active'] ?? '')==='1')?'selected':'' ?>>Active</option>
                <option value="0" <?= (($filters['active'] ?? '')==='0')?'selected':'' ?>>Inactive</option>
              </select>
            </div>
            <div class="col-md-1">
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
          <h5 class="card-title mb-0"><i class="fas fa-plus me-2"></i>Create Item</h5>
        </div>
        <div class="card-body">
      <form method="post" action="/admin/recommendations/items" id="create-item-form">
        <div class="row g-2 align-items-end">
          <div class="col-md-4 position-relative">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" id="state-input" autocomplete="off" required />
            <div id="states-menu" class="list-group position-absolute w-100 border bg-white" style="z-index:1000; display:none; max-height:240px; overflow:auto;"></div>
          </div>
          <div class="col-md-2">
            <label class="form-label">Action</label>
            <select name="action_code" class="form-select" required>
              <?php foreach ([101=>'Reward',102=>'Produk',103=>'Hukuman',105=>'Misi',106=>'Coaching'] as $code=>$label): ?>
                <option value="<?= $code ?>"><?= $code ?> - <?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Ref Type</label>
            <select name="ref_type" class="form-select" id="ref-type-select" required>
              <?php foreach (['mission','product','reward','coaching','punishment'] as $rt): ?>
                <option value="<?= $rt ?>"><?= $rt ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2 position-relative">
            <label class="form-label">Ref Search</label>
            <input type="text" class="form-control" id="ref-search" autocomplete="off" placeholder="type to search..." />
            <div id="refs-menu" class="list-group position-absolute w-100 border bg-white" style="z-index:1000; display:none; max-height:240px; overflow:auto;"></div>
            <input type="hidden" name="ref_id" id="ref-id" required />
          </div>
          <div class="col-md-1">
            <label class="form-label">Active</label>
            <select name="is_active" class="form-select">
              <option value="1" selected>Yes</option>
              <option value="0">No</option>
            </select>
          </div>
          <div class="col-md-1">
            <button class="btn btn-success w-100" type="submit"><i class="fas fa-plus me-1"></i>Add</button>
          </div>
        </div>
      </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row pm-section">
    <?php
      // Build columns for the reusable table
      $columns = [
        ['label' => 'ID', 'key' => 'id'],
        ['label' => 'State', 'key' => 'state'],
        ['label' => 'Action', 'key' => 'action_code'],
        [
          'label' => 'Ref',
          'key' => 'ref',
          'formatter' => function($_, $row) {
            $base = ($row['ref_type'] ?? '') . '#' . (string)($row['ref_id'] ?? '');
            if (!empty($row['ref_title'])) {
              return htmlspecialchars($row['ref_title']) . ' <small class="text-muted">(' . htmlspecialchars($base) . ')</small>';
            }
            return htmlspecialchars($base);
          }
        ],
        [
          'label' => 'Active',
          'key' => 'is_active',
          'formatter' => function($v) { return !empty($v) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; }
        ],
      ];

      // Build actions for each row using GET quick actions handled in controller
      $actions = [
        [
          'label' => 'Activate', 'icon' => 'fas fa-toggle-on', 'class' => 'btn-outline-success',
          'condition' => function($row){ return empty($row['is_active']); },
          'attributes' => function($row) use ($filters) {
            $qs = array_merge($filters, ['do' => 'toggle', 'id' => (int)$row['id'], 'to' => 1]);
            return ['href' => '/admin/recommendations/items?' . http_build_query($qs)];
          }
        ],
        [
          'label' => 'Deactivate', 'icon' => 'fas fa-toggle-off', 'class' => 'btn-outline-secondary',
          'condition' => function($row){ return !empty($row['is_active']); },
          'attributes' => function($row) use ($filters) {
            $qs = array_merge($filters, ['do' => 'toggle', 'id' => (int)$row['id'], 'to' => 0]);
            return ['href' => '/admin/recommendations/items?' . http_build_query($qs)];
          }
      ],
      [
        'label' => 'Edit', 'icon' => 'fas fa-edit', 'class' => 'btn-outline-primary js-edit-item',
        'attributes' => function($row){
          return [
            'data-id' => (int)$row['id'],
            'data-state' => (string)($row['state'] ?? ''),
            'data-action' => (int)($row['action_code'] ?? 0),
            'data-ref-type' => (string)($row['ref_type'] ?? ''),
            'data-ref-id' => (int)($row['ref_id'] ?? 0),
            'data-active' => !empty($row['is_active']) ? '1' : '0',
          ];
        }
      ],
      [
        'label' => 'Delete', 'icon' => 'fas fa-trash', 'class' => 'btn-outline-danger',
        'attributes' => function($row) use ($filters) {
          $qs = array_merge($filters, ['do' => 'delete', 'id' => (int)$row['id']]);
          return ['href' => '/admin/recommendations/items?' . http_build_query($qs), 'onclick' => "return confirm('Delete this item?');"];
          }
        ],
      ];

      // Build pagination expected by the component
      $total = (int)($meta['total'] ?? 0); $limit=(int)($meta['limit'] ?? 20); $offset=(int)($meta['offset'] ?? 0);
      $current_page = ($limit > 0) ? (int)floor($offset / $limit) + 1 : 1;
      $total_pages = ($limit > 0) ? (int)ceil($total / $limit) : 1;
      $start_record = ($total === 0) ? 0 : ($offset + 1);
      $end_record = min($offset + $limit, $total);
      $base_params = $filters;

      $renderer->includePartial('components/partials/table', [
        'columns' => $columns,
        'actions' => $actions,
        'data' => $items,
        'pagination' => [
          'current_page' => $current_page,
          'total_pages' => $total_pages,
          'total_records' => $total,
          'start_record' => $start_record,
          'end_record' => $end_record,
          'base_params' => $base_params,
        ],
        'empty_message' => 'No items found.',
      ]);
    ?>
  </div>

  <script>
    (function(){
      const stateInput = document.getElementById('state-input');
      const statesMenu = document.getElementById('states-menu');
      let stateTimer;
      stateInput && stateInput.addEventListener('input', function(){
        const q = this.value.trim();
        clearTimeout(stateTimer);
        stateTimer = setTimeout(() => {
          if (q.length < 1) { statesMenu.style.display='none'; statesMenu.innerHTML = ''; return; }
          fetch('/admin/recommendations/items/typeahead/states?q=' + encodeURIComponent(q))
            .then(r => r.json()).then(d => {
              const items = (d && (d.states || (d.data && d.data.states))) || [];
              if (!items.length) { statesMenu.style.display='none'; statesMenu.innerHTML=''; return; }
              statesMenu.innerHTML = items.map(s => `<button type="button" class="list-group-item list-group-item-action" data-value="${s}">${s}</button>`).join('');
              statesMenu.style.display = 'block';
              Array.from(statesMenu.querySelectorAll('button')).forEach(btn => {
                btn.addEventListener('click', () => {
                  stateInput.value = btn.getAttribute('data-value');
                  statesMenu.style.display = 'none';
                  statesMenu.innerHTML = '';
                });
              });
            }).catch(()=>{});
        }, 200);
      });
      document.addEventListener('click', (e)=>{
        if (!statesMenu.contains(e.target) && e.target !== stateInput) {
          statesMenu.style.display='none';
        }
      });

      const refTypeSel = document.getElementById('ref-type-select');
      const refSearch = document.getElementById('ref-search');
      const refsMenu = document.getElementById('refs-menu');
      const refIdHidden = document.getElementById('ref-id');
      let refTimer;
      function doRefSearch(){
        const q = refSearch.value.trim();
        const t = refTypeSel.value;
        if (!t) return;
        clearTimeout(refTimer);
        refTimer = setTimeout(() => {
          if (q.length < 1) { refsMenu.style.display='none'; refsMenu.innerHTML = ''; return; }
          fetch('/admin/recommendations/items/typeahead/refs?ref_type=' + encodeURIComponent(t) + '&q=' + encodeURIComponent(q))
            .then(r => r.json()).then(d => {
              const items = (d && (d.refs || (d.data && d.data.refs))) || [];
              if (!items.length) { refsMenu.style.display='none'; refsMenu.innerHTML=''; return; }
              refsMenu.innerHTML = items.map(it => `<button type="button" class="list-group-item list-group-item-action" data-id="${it.id}" data-title="${it.title}">${it.title}</button>`).join('');
              refsMenu.style.display = 'block';
              Array.from(refsMenu.querySelectorAll('button')).forEach(btn => {
                btn.addEventListener('click', () => {
                  refSearch.value = btn.getAttribute('data-title');
                  refIdHidden.value = btn.getAttribute('data-id');
                  refsMenu.style.display = 'none';
                  refsMenu.innerHTML = '';
                });
              });
            }).catch(()=>{});
        }, 200);
      }
      refSearch && refSearch.addEventListener('input', doRefSearch);
      refTypeSel && refTypeSel.addEventListener('change', () => { refSearch.value=''; refIdHidden.value=''; refsMenu.innerHTML=''; refsMenu.style.display='none'; });
      
      // Edit modal logic: build and wire modal
      (function(){
        const tpl = `
<div class=\"modal fade\" id=\"editItemModal\" tabindex=\"-1\" aria-hidden=\"true\">
  <div class=\"modal-dialog modal-lg modal-dialog-scrollable\">
    <div class=\"modal-content\">
      <div class=\"modal-header\">
        <h5 class=\"modal-title\"><i class=\"fas fa-edit me-2\"></i>Edit Item</h5>
        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
      </div>
      <form method=\"post\" action=\"/admin/recommendations/items/update\" id=\"edit-item-form\">
        <div class=\"modal-body\">
          <input type=\"hidden\" name=\"id\" id=\"edit-id\" />
          <div class=\"row g-2\">
            <div class=\"col-md-6 position-relative\">
              <label class=\"form-label\">State</label>
              <input type=\"text\" name=\"state\" class=\"form-control\" id=\"edit-state\" autocomplete=\"off\" required />
              <div id=\"edit-states-menu\" class=\"list-group position-absolute w-100 border bg-white\" style=\"z-index:1000; display:none; max-height:240px; overflow:auto;\"></div>
            </div>
            <div class=\"col-md-3\">
              <label class=\"form-label\">Action</label>
              <select name=\"action_code\" class=\"form-select\" id=\"edit-action\" required>
                <option value=\"101\">101 - Reward</option>
                <option value=\"102\">102 - Produk</option>
                <option value=\"103\">103 - Hukuman</option>
                <option value=\"105\">105 - Misi</option>
                <option value=\"106\">106 - Coaching</option>
              </select>
            </div>
            <div class=\"col-md-3\">
              <label class=\"form-label\">Ref Type</label>
              <select name=\"ref_type\" class=\"form-select\" id=\"edit-ref-type\" required>
                <option value=\"mission\">mission</option>
                <option value=\"product\">product</option>
                <option value=\"reward\">reward</option>
                <option value=\"coaching\">coaching</option>
                <option value=\"punishment\">punishment</option>
              </select>
            </div>
            <div class=\"col-md-6 position-relative\">
              <label class=\"form-label\">Ref Search</label>
              <input type=\"text\" class=\"form-control\" id=\"edit-ref-search\" autocomplete=\"off\" placeholder=\"type to search...\" />
              <div id=\"edit-refs-menu\" class=\"list-group position-absolute w-100 border bg-white\" style=\"z-index:1000; display:none; max-height:240px; overflow:auto;\"></div>
              <input type=\"hidden\" name=\"ref_id\" id=\"edit-ref-id\" required />
            </div>
            <div class=\"col-md-3\">
              <label class=\"form-label\">Active</label>
              <select name=\"is_active\" class=\"form-select\" id=\"edit-active\">
                <option value=\"1\">Yes</option>
                <option value=\"0\">No</option>
              </select>
            </div>
          </div>
        </div>
        <div class=\"modal-footer\">
          <button type=\"button\" class=\"btn btn-outline-secondary\" data-bs-dismiss=\"modal\">Cancel</button>
          <button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-save me-1\"></i>Save</button>
        </div>
      </form>
    </div>
  </div>
</div>`;
        const wrap = document.createElement('div');
        wrap.innerHTML = tpl;
        document.body.appendChild(wrap);
        let bsModal = null;
        const modalEl = document.getElementById('editItemModal');
        function showModal(){ if (!bsModal && window.bootstrap) bsModal = new bootstrap.Modal(modalEl); bsModal && bsModal.show(); }

        // Bind edit buttons
        Array.from(document.querySelectorAll('.js-edit-item')).forEach(btn => {
          btn.addEventListener('click', function(){
            document.getElementById('edit-id').value = this.getAttribute('data-id');
            document.getElementById('edit-state').value = this.getAttribute('data-state') || '';
            document.getElementById('edit-action').value = this.getAttribute('data-action') || '';
            document.getElementById('edit-ref-type').value = this.getAttribute('data-ref-type') || '';
            document.getElementById('edit-ref-id').value = this.getAttribute('data-ref-id') || '';
            document.getElementById('edit-ref-search').value = '';
            document.getElementById('edit-active').value = (this.getAttribute('data-active') === '1') ? '1' : '0';
            showModal();
          });
        });

        // Typeahead inside modal
        const mState = document.getElementById('edit-state');
        const mStatesMenu = document.getElementById('edit-states-menu');
        let tm;
        mState && mState.addEventListener('input', function(){
          const q = this.value.trim();
          clearTimeout(tm);
          tm = setTimeout(()=>{
            if (!q){ mStatesMenu.style.display='none'; mStatesMenu.innerHTML=''; return; }
            fetch('/admin/recommendations/items/typeahead/states?q=' + encodeURIComponent(q))
              .then(r=>r.json()).then(d=>{
                const items = (d && (d.states || (d.data && d.data.states))) || [];
                if (!items.length){ mStatesMenu.style.display='none'; mStatesMenu.innerHTML=''; return; }
                mStatesMenu.innerHTML = items.map(s => `<button type="button" class="list-group-item list-group-item-action" data-value="${s}">${s}</button>`).join('');
                mStatesMenu.style.display='block';
                Array.from(mStatesMenu.querySelectorAll('button')).forEach(b=>{
                  b.addEventListener('click',()=>{
                    mState.value = b.getAttribute('data-value');
                    mStatesMenu.style.display='none'; mStatesMenu.innerHTML='';
                  });
                });
              }).catch(()=>{});
          }, 200);
        });
        document.addEventListener('click', (e)=>{ if (!mStatesMenu.contains(e.target) && e.target !== mState){ mStatesMenu.style.display='none'; }});

        const mRefType = document.getElementById('edit-ref-type');
        const mRefSearch = document.getElementById('edit-ref-search');
        const mRefsMenu = document.getElementById('edit-refs-menu');
        const mRefId = document.getElementById('edit-ref-id');
        let tr2;
        function doMRef(){
          const q = (mRefSearch.value || '').trim();
          const tval = mRefType.value;
          clearTimeout(tr2);
          tr2 = setTimeout(()=>{
            if (!tval || q.length < 1){ mRefsMenu.style.display='none'; mRefsMenu.innerHTML=''; return; }
            fetch('/admin/recommendations/items/typeahead/refs?ref_type=' + encodeURIComponent(tval) + '&q=' + encodeURIComponent(q))
              .then(r=>r.json()).then(d=>{
                const items = (d && (d.refs || (d.data && d.data.refs))) || [];
                if (!items.length){ mRefsMenu.style.display='none'; mRefsMenu.innerHTML=''; return; }
                mRefsMenu.innerHTML = items.map(it => `<button type="button" class="list-group-item list-group-item-action" data-id="${it.id}" data-title="${it.title}">${it.title}</button>`).join('');
                mRefsMenu.style.display='block';
                Array.from(mRefsMenu.querySelectorAll('button')).forEach(b=>{
                  b.addEventListener('click',()=>{
                    mRefSearch.value = b.getAttribute('data-title');
                    mRefId.value = b.getAttribute('data-id');
                    mRefsMenu.style.display='none'; mRefsMenu.innerHTML='';
                  });
                });
              }).catch(()=>{});
          }, 200);
        }
        mRefSearch && mRefSearch.addEventListener('input', doMRef);
        mRefType && mRefType.addEventListener('change', ()=>{ mRefSearch.value=''; mRefId.value=''; mRefsMenu.innerHTML=''; mRefsMenu.style.display='none'; });
        document.addEventListener('click', (e)=>{ if (!mRefsMenu.contains(e.target) && e.target !== mRefSearch){ mRefsMenu.style.display='none'; }});
      })();
      document.addEventListener('click', (e)=>{
        if (!refsMenu.contains(e.target) && e.target !== refSearch) {
          refsMenu.style.display='none';
        }
      });

      // Filters: ref typeahead
      const filterRefType = document.getElementById('filter-ref-type');
      const filterRefSearch = document.getElementById('filter-ref-search');
      const filterRefsMenu = document.getElementById('filter-refs-menu');
      const filterRefId = document.getElementById('filter-ref-id');
      let filterTimer;
      function doFilterRefSearch(){
        const q = (filterRefSearch.value || '').trim();
        const t = (filterRefType && filterRefType.value) || '';
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => {
          if (!t || q.length < 1) { filterRefsMenu.style.display='none'; filterRefsMenu.innerHTML=''; return; }
          fetch('/admin/recommendations/items/typeahead/refs?ref_type=' + encodeURIComponent(t) + '&q=' + encodeURIComponent(q))
            .then(r => r.json()).then(d => {
              const items = (d && (d.refs || (d.data && d.data.refs))) || [];
              if (!items.length) { filterRefsMenu.style.display='none'; filterRefsMenu.innerHTML=''; return; }
              filterRefsMenu.innerHTML = items.map(it => `<button type="button" class="list-group-item list-group-item-action" data-id="${it.id}" data-title="${it.title}">${it.title}</button>`).join('');
              filterRefsMenu.style.display = 'block';
              Array.from(filterRefsMenu.querySelectorAll('button')).forEach(btn => {
                btn.addEventListener('click', () => {
                  filterRefSearch.value = btn.getAttribute('data-title');
                  filterRefId.value = btn.getAttribute('data-id');
                  filterRefsMenu.style.display = 'none';
                  filterRefsMenu.innerHTML = '';
                });
              });
            }).catch(()=>{});
        }, 200);
      }
      filterRefSearch && filterRefSearch.addEventListener('input', doFilterRefSearch);
      filterRefType && filterRefType.addEventListener('change', ()=>{ filterRefSearch.value=''; filterRefId.value=''; filterRefsMenu.innerHTML=''; filterRefsMenu.style.display='none'; });
      document.addEventListener('click', (e)=>{
        if (filterRefsMenu && !filterRefsMenu.contains(e.target) && e.target !== filterRefSearch) {
          filterRefsMenu.style.display='none';
        }
      });
    })();
  </script>
</div>
