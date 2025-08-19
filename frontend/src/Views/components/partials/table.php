<?php
/**
 * A reusable table component.
 *
 * @var array $columns Defines the table columns.
 *  - 'label': The header label.
 *  - 'key': The key to access data in the row array.
 *  - 'formatter': (Optional) A callback function to format the cell value.
 *  - 'type': (Optional) Can be 'index' for a running number.
 * @var array $actions Defines the action buttons for each row.
 *  - 'label': The button text.
 *  - 'icon': (Optional) The Font Awesome icon class.
 *  - 'class': The button's CSS class.
 *  - 'attributes': (Optional) An array or callback to generate HTML attributes.
 *  - 'condition': (Optional) A callback to determine if the button should be shown.
 * @var array $data The array of data rows to display.
 * @var array $pagination Pagination data.
 *  - 'current_page', 'total_pages', 'total_records', 'start_record', 'end_record', 'base_params'.
 * @var string $empty_message The message to display if $data is empty.
 */

// Helper function to build query strings for pagination
if (!function_exists('build_query_string')) {
    function build_query_string(array $params): string
    {
        return http_build_query(array_filter($params));
    }
}
?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-borderless">
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <th><?= htmlspecialchars($column['label']); ?></th>
                        <?php endforeach; ?>
                        <?php if (!empty($actions)): ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="<?= count($columns) + (!empty($actions) ? 1 : 0); ?>">
                                <?= htmlspecialchars($empty_message ?? 'No data found.'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = $pagination['start_record'] ?? 1; ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($columns as $column): ?>
                                    <td>
                                        <?php
                                        if (isset($column['type']) && $column['type'] === 'index') {
                                            echo $i++;
                                        } elseif (isset($column['formatter']) && is_callable($column['formatter'])) {
                                            echo $column['formatter']($row[$column['key']], $row);
                                        } else {
                                            echo htmlspecialchars($row[$column['key']] ?? '');
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>

                                <?php if (!empty($actions)): ?>
                                    <td>
                                        <?php foreach ($actions as $action): ?>
                                            <?php
                                            $show_action = !isset($action['condition']) || (isset($action['condition']) && is_callable($action['condition']) && $action['condition']($row));
                                            if (!$show_action) continue;

                                            $attributes_str = '';
                                            if (isset($action['attributes'])) {
                                                $attrs = is_callable($action['attributes']) ? $action['attributes']($row) : $action['attributes'];
                                                foreach ($attrs as $key => $value) {
                                                    $attributes_str .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
                                                }
                                            }
                                            ?>
                                            <button type="button" class="btn <?= htmlspecialchars($action['class']); ?> btn-sm"<?= $attributes_str; ?>>
                                                <?php if (isset($action['icon'])): ?>
                                                    <i class="<?= htmlspecialchars($action['icon']); ?>"></i>
                                                <?php endif; ?>
                                                <span class="d-none d-md-inline"> <?= htmlspecialchars($action['label']); ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
<nav class="d-flex justify-content-between align-items-center" aria-label="Page navigation">
    <div class="mb-3">
        Showing <?= htmlspecialchars($pagination['start_record']); ?> to <?= htmlspecialchars($pagination['end_record']); ?> of <?= htmlspecialchars($pagination['total_records']); ?> entries
    </div>
    <ul class="pagination mb-0">
        <!-- Previous Button -->
        <li class="page-item <?= ($pagination['current_page'] <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?= build_query_string(array_merge($pagination['base_params'], ['page' => $pagination['current_page'] - 1])); ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>

        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
            <li class="page-item <?= ($p == $pagination['current_page']) ? 'active' : ''; ?>">
                <a class="page-link" href="?<?= build_query_string(array_merge($pagination['base_params'], ['page' => $p])); ?>">
                    <?= $p; ?>
                </a>
            </li>
        <?php endfor; ?>

        <!-- Next Button -->
        <li class="page-item <?= ($pagination['current_page'] >= $pagination['total_pages']) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?= build_query_string(array_merge($pagination['base_params'], ['page' => $pagination['current_page'] + 1])); ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>
