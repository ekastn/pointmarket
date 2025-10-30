<?php /** @var array|null $trace */ ?>
<div class="container">
  <h1>Recommendations Trace</h1>
  <form method="get" class="mb-3">
    <label for="student_id">Student ID</label>
    <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id ?? '') ?>" required>
    <button type="submit">Load Trace</button>
  </form>

  <?php if (!empty($trace)): ?>
    <?php $t = $trace['trace'] ?? []; ?>
    <section>
      <h2>Summary</h2>
      <p><strong>Student:</strong> <?= htmlspecialchars($trace['siswa_id'] ?? '') ?></p>
      <p><strong>State:</strong> <?= htmlspecialchars($trace['current_state'] ?? '') ?></p>
      <p><strong>Timestamp:</strong> <?= htmlspecialchars(($t['timestamp'] ?? '')) ?></p>
      <p><strong>Source:</strong> <?= htmlspecialchars(($t['source'] ?? '')) ?></p>
    </section>

    <section>
      <h2>Q-Values</h2>
      <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Action</th><th>Q-Value</th></tr>
        <?php foreach (($t['q_values'] ?? []) as $ac => $q): ?>
          <tr><td><?= htmlspecialchars((string)$ac) ?></td><td><?= htmlspecialchars(number_format((float)$q, 4)) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </section>

    <section>
      <h2>CBF Top Items</h2>
      <?php foreach (($t['cbf']['actions'] ?? []) as $a): ?>
        <h3>Action <?= htmlspecialchars((string)($a['action_code'] ?? '')) ?></h3>
        <p>Pool (state): <?= (int)($a['pool_size_state'] ?? 0) ?>, Pool (fallback): <?= (int)($a['pool_size_action_fallback'] ?? 0) ?></p>
        <table border="1" cellpadding="6" cellspacing="0">
          <tr><th>Ref Type</th><th>Ref ID</th><th>Item State</th><th>CBF Score</th></tr>
          <?php foreach (($a['top_items'] ?? []) as $it): ?>
            <tr>
              <td><?= htmlspecialchars((string)($it['ref_type'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)($it['ref_id'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)($it['item_state'] ?? '')) ?></td>
              <td><?= htmlspecialchars(number_format((float)($it['cbf_score'] ?? 0), 4)) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endforeach; ?>
    </section>

    <section>
      <h2>Recent Rewards</h2>
      <?php foreach (($t['rewards'] ?? []) as $rw): ?>
        <h3>Action <?= htmlspecialchars((string)($rw['action_code'] ?? '')) ?></h3>
        <table border="1" cellpadding="6" cellspacing="0">
          <tr><th>Timestamp</th><th>State</th><th>Reward</th></tr>
          <?php foreach (($rw['recent'] ?? []) as $r): ?>
            <tr>
              <td><?= htmlspecialchars((string)($r['timestamp'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)($r['state'] ?? '')) ?></td>
              <td><?= htmlspecialchars(number_format((float)($r['reward'] ?? 0), 4)) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endforeach; ?>
    </section>
  <?php elseif (!empty($student_id)): ?>
    <p>No trace found or error retrieving trace.</p>
  <?php endif; ?>
</div>

