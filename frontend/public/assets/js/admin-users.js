const userTable = document.querySelector('.table');

if (userTable) {
    userTable.addEventListener('click', function (event) {
        const deleteButton = event.target.closest('.btn-hapus');
        const editButton = event.target.closest('.btn-edit-user');

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
