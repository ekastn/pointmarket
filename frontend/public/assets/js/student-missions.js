document.addEventListener('DOMContentLoaded', function () {
    const updateStatusModal = document.getElementById('updateStatusModal');
    const updateStatusForm = document.getElementById('updateStatusForm');
    const userMissionIdInput = document.getElementById('userMissionId');
    const missionStatusSelect = document.getElementById('missionStatus');
    const missionProgressInput = document.getElementById('missionProgress');

    // Handle Start/Continue Mission button click (delegated)
    document.body.addEventListener('click', async function (event) {
        const startButton = event.target.closest('.start-mission-btn');
        if (startButton) {
            const missionId = startButton.dataset.missionId;
            const userMissionId = startButton.dataset.userMissionId; // This might be 0 if it's a new start

            if (userMissionId && userMissionId !== '0') {
                // If userMissionId exists, it means it's a continue action, just reload or show message
                alert('Mission already started. Continuing...');
                window.location.reload();
                return;
            }

            if (confirm('Are you sure you want to start this mission?')) {
                try {
                    const response = await fetch(`/missions/${missionId}/start`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ mission_id: parseInt(missionId) }),
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
                    alert('An error occurred while starting the mission.');
                }
            }
        }
    });

    // Handle Update Status button click (delegated)
    document.body.addEventListener('click', function (event) {
        const updateButton = event.target.closest('.update-status-btn');
        if (updateButton) {
            const userMissionId = updateButton.dataset.userMissionId;
            userMissionIdInput.value = userMissionId;
            // You might want to fetch current status and progress to pre-fill the modal
            // For now, it starts with default values
        }
    });

    // Form submission for Update Status
    updateStatusForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const userMissionId = userMissionIdInput.value;
        const statusData = {
            status: missionStatusSelect.value,
            progress: missionProgressInput.value ? parseInt(missionProgressInput.value) : null,
        };

        try {
            const response = await fetch(`/missions/${userMissionId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PUT' // For PHP to recognize PUT
                },
                body: JSON.stringify(statusData),
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
            alert('An error occurred while updating mission status.');
        }
    });
});
