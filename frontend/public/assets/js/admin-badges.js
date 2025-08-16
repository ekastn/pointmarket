document.addEventListener('DOMContentLoaded', function () {
    const modalTambahBadge = document.getElementById('modalTambahBadge');
    const modalEditBadge = document.getElementById('modalEditBadge');
    const modalAwardBadge = document.getElementById('modalAwardBadge');
    const editBadgeForm = document.getElementById('edit-badge-form');
    const awardBadgeForm = document.getElementById('award-badge-form');
    const badgeTable = document.querySelector('.table-responsive');

    // Handle Edit button click to populate modal
    if (badgeTable) {
        badgeTable.addEventListener('click', function (event) {
            const editButton = event.target.closest('.btn-edit-badge');
            if (editButton) {
                const badgeId = editButton.dataset.badgeId;
                const badgeTitle = editButton.dataset.badgeTitle;
                const badgeDescription = editButton.dataset.badgeDescription;
                const badgeCriteria = editButton.dataset.badgeCriteria;
                const badgeRepeatable = editButton.dataset.badgeRepeatable;

                document.getElementById('edit-badge-id').value = badgeId;
                document.getElementById('edit-title').value = badgeTitle;
                document.getElementById('edit-description').value = badgeDescription;
                document.getElementById('edit-criteria').value = badgeCriteria;
                document.getElementById('edit-repeatable').checked = badgeRepeatable === '1';

                // Set form action for PUT request
                editBadgeForm.action = `/badges/${badgeId}`;
            }
        });
    }

    // Handle Award button click to populate modal
    if (badgeTable) {
        badgeTable.addEventListener('click', function (event) {
            const awardButton = event.target.closest('.btn-award-badge');
            if (awardButton) {
                const badgeId = awardButton.dataset.badgeId;
                document.getElementById('award-badge-id').value = badgeId;
            }
        });
    }

    // Handle Edit Badge form submission (AJAX PUT request)
    if (editBadgeForm) {
        editBadgeForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const badgeId = document.getElementById('edit-badge-id').value;
            const formData = new FormData(this);
            const jsonData = {};

            for (let [key, value] of formData.entries()) {
                if (key !== '_method' && key !== 'id') {
                    jsonData[key] = value;
                }
            }
            jsonData['repeatable'] = document.getElementById('edit-repeatable').checked;

            try {
                const response = await fetch(`/badges/${badgeId}`, {
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
                alert('An error occurred while updating the badge.');
            }
        });
    }

    // Handle Award Badge form submission (AJAX POST request)
    if (awardBadgeForm) {
        awardBadgeForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            const jsonData = {};

            for (let [key, value] of formData.entries()) {
                jsonData[key] = value;
            }

            try {
                const response = await fetch('/badges/award', {
                    method: 'POST',
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
                alert('An error occurred while awarding the badge.');
            }
        });
    }

    // Handle Delete Badge button click (AJAX DELETE request)
    if (badgeTable) {
        badgeTable.addEventListener('click', async function (event) {
            const deleteButton = event.target.closest('.btn-delete-badge');
            if (deleteButton) {
                const badgeId = deleteButton.dataset.badgeId;
                if (confirm('Are you sure you want to delete this badge?')) {
                    try {
                        const response = await fetch(`/badges/${badgeId}`, {
                            method: 'DELETE',
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
                        alert('An error occurred while deleting the badge.');
                    }
                }
            }
        });
    }
});
