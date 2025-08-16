document.addEventListener('DOMContentLoaded', function () {
    const missionModal = document.getElementById('missionModal');
    const missionForm = document.getElementById('missionForm');
    const missionIdInput = document.getElementById('missionId');
    const missionTitleInput = document.getElementById('missionTitle');
    const missionDescriptionInput = document.getElementById('missionDescription');
    const missionRewardPointsInput = document.getElementById('missionRewardPoints');
    const missionMetadataInput = document.getElementById('missionMetadata');
    const missionModalLabel = document.getElementById('missionModalLabel');
    const missionTable = document.getElementById('missionsTable');

    // Function to reset form
    function resetForm() {
        missionForm.reset();
        missionIdInput.value = '';
        missionModalLabel.textContent = 'Add New Mission';
    }

    // Add Mission button click
    document.getElementById('addMissionBtn').addEventListener('click', resetForm);

    // Edit Mission button click (delegated)
    if (missionTable) {
        missionTable.addEventListener('click', function (event) {
            const editButton = event.target.closest('.edit-mission-btn');
            if (editButton) {
                missionIdInput.value = editButton.dataset.id;
                missionTitleInput.value = editButton.dataset.title;
                missionDescriptionInput.value = editButton.dataset.description;
                missionRewardPointsInput.value = editButton.dataset.rewardPoints;
                missionMetadataInput.value = editButton.dataset.metadata;
                missionModalLabel.textContent = 'Edit Mission';
            }
        });
    }

    // Form submission for Add/Edit Mission
    missionForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const id = missionIdInput.value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/missions/${id}` : '/missions';

        const missionData = {
            title: missionTitleInput.value,
            description: missionDescriptionInput.value || null,
            reward_points: missionRewardPointsInput.value ? parseInt(missionRewardPointsInput.value) : null,
            metadata: missionMetadataInput.value ? JSON.parse(missionMetadataInput.value) : null,
        };

        try {
            await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': method // For PHP to recognize PUT/DELETE
                },
                body: JSON.stringify(missionData),
            });

            window.location.reload(); // Reload page to see changes
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving the mission.' + error);
        }
    });

    // Delete Mission button click (delegated)
    if (missionTable) {
        missionTable.addEventListener('click', async function (event) {
            const deleteButton = event.target.closest('.delete-mission-btn');
            if (deleteButton) {
                const id = deleteButton.dataset.id;
                if (confirm('Are you sure you want to delete this mission?')) {
                    try {
                        const response = await fetch(`/missions/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-HTTP-Method-Override': 'DELETE' // For PHP to recognize DELETE
                            },
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
                        alert('An error occurred while deleting the mission.');
                    }
                }
            }
        });
    }

    // Search functionality
    const missionSearchInput = document.getElementById('missionSearch');
    if (missionSearchInput) {
        missionSearchInput.addEventListener('keyup', function (event) {
            if (event.key === 'Enter') {
                const searchValue = missionSearchInput.value;
                window.location.href = `/missions?search=${encodeURIComponent(searchValue)}`;
            }
        });
    }
});
