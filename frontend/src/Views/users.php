<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="/public/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <h1>User Management</h1>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <select onchange="updateUserRole(<?= $user['id'] ?>, this.value)">
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                            <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                        </select>
                    </td>
                    <td>
                        <button onclick="deleteUser(<?= $user['id'] ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        async function updateUserRole(userId, role) {
            const response = await fetch('/admin/users/update-role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: userId, role: role })
            });
            const result = await response.json();
            if (result.success) {
                alert('User role updated successfully!');
            } else {
                alert('Error updating user role: ' + result.message);
            }
        }

        async function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user?')) {
                return;
            }
            const response = await fetch('/admin/users/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: userId })
            });
            const result = await response.json();
            if (result.success) {
                alert('User deleted successfully!');
                location.reload(); // Reload page to reflect changes
            } else {
                alert('Error deleting user: ' + result.message);
            }
        }
    </script>
</body>
</html>
