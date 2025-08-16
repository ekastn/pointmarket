document.addEventListener('DOMContentLoaded', function () {
    const courseTable = document.querySelector('.table-responsive');

    // Handle Unenroll button click (AJAX DELETE request)
    if (courseTable) {
        courseTable.addEventListener('click', async function (event) {
            const unenrollButton = event.target.closest('.btn-unenroll');
            if (unenrollButton) {
                const courseId = unenrollButton.dataset.courseId;
                if (confirm('Are you sure you want to unenroll from this course?')) {
                    try {
                        const response = await fetch(`/courses/${courseId}/unenroll`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ course_id: courseId, user_id: <?php echo $_SESSION['user_data']['id'] ?? 0; ?> }),
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
                        alert('An error occurred while unenrolling from the course.');
                    }
                }
            }
        });
    }

    // Handle Enroll button click (AJAX POST request) - assuming there will be an enroll button on a separate page or section
    // This part is conceptual as the 'siswa/courses.php' only shows enrolled courses.
    // If there's a page to browse all available courses, this logic would go there.
    const enrollButton = document.querySelector('.btn-enroll'); // Example selector
    if (enrollButton) {
        enrollButton.addEventListener('click', async function (event) {
            const courseId = enrollButton.dataset.courseId;
            if (confirm('Are you sure you want to enroll in this course?')) {
                try {
                    const response = await fetch(`/courses/${courseId}/enroll`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ course_id: courseId, user_id: <?php echo $_SESSION['user_data']['id'] ?? 0; ?> }),
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
                    alert('An error occurred while enrolling in the course.');
                }
            }
        });
    }
});
