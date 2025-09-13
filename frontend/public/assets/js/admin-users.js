const userTable = document.querySelector('.table');

if (userTable) {
    userTable.addEventListener('click', function (event) {
        const deleteButton = event.target.closest('.btn-hapus');
        const editButton = event.target.closest('.btn-edit-user');
        const statsButton = event.target.closest('.btn-user-stats');

        // Handle delete button clicks
        if (deleteButton) {
            const userId = deleteButton.dataset.userId;

            if (confirm('Are you sure you want to delete this user?')) {
                fetch(`/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(window.location.reload())
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the user.');
                });
            }
        } else if (editButton) {
            document.getElementById('edit-user-id').value = editButton.dataset.userId;
            document.getElementById('edit-name').value = editButton.dataset.userName;
            document.getElementById('edit-username').value = editButton.dataset.userUsername;
            document.getElementById('edit-email').value = editButton.dataset.userEmail;
            document.getElementById('edit-role').value = editButton.dataset.userRole;
        } else if (statsButton) {
            const userId = statsButton.dataset.userId;
            document.getElementById('stats-user-id').value = userId;
            fetch(`/users/${userId}/stats`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('stats-total-points').value = data.data.total_points ?? '-';
                } else {
                    alert(data.message || 'Failed to fetch user stats');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Failed to fetch user stats');
            });
        }
    });
}

// Handle edit user form submission
const editUserForm = document.getElementById('edit-user-form');

if (editUserForm) {
    editUserForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        const userId = document.getElementById('edit-user-id').value;
        const name = document.getElementById('edit-name').value;
        const username = document.getElementById('edit-username').value;
        const email = document.getElementById('edit-email').value;
        const role = document.getElementById('edit-role').value;

        fetch(`/users/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                name: name,
                username: username,
                email: email,
                role: role
            })
        })
        .then(window.location.reload())
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the user.');
        });
    });
}

// Adjust stats form
const adjustForm = document.getElementById('formAdjustStats');
if (adjustForm) {
    adjustForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const userId = document.getElementById('stats-user-id').value;
        const delta = parseInt(document.getElementById('stats-delta').value || '0', 10);
        const reason = document.getElementById('stats-reason').value || undefined;
        if (!delta || delta === 0) {
            alert('Delta must be non-zero');
            return;
        }
        fetch(`/users/${userId}/stats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ delta, reason })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stats-total-points').value = data.data.total_points ?? '-';
                adjustForm.reset();
            } else {
                alert(data.message || 'Failed to adjust user stats');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to adjust user stats');
        });
    });
}
