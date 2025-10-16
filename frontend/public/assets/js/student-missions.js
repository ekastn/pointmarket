document.addEventListener('DOMContentLoaded', function () {
    // Manual status update disabled: no modal or form bindings

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

    // Manual status update disabled: removed update status event handlers
});
