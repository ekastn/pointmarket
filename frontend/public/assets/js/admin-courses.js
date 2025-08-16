document.addEventListener('DOMContentLoaded', function () {
    const modalTambahCourse = document.getElementById('modalTambahCourse');
    const modalEditCourse = document.getElementById('modalEditCourse');
    const editCourseForm = document.getElementById('edit-course-form');
    const courseTable = document.querySelector('.table-responsive');

    // Handle Edit button click to populate modal
    if (courseTable) {
        courseTable.addEventListener('click', function (event) {
            const editButton = event.target.closest('.btn-edit-course');
            if (editButton) {
                const courseId = editButton.dataset.courseId;
                const courseTitle = editButton.dataset.courseTitle;
                const courseSlug = editButton.dataset.courseSlug;
                const courseDescription = editButton.dataset.courseDescription;
                const courseMetadata = editButton.dataset.courseMetadata;

                document.getElementById('edit-course-id').value = courseId;
                document.getElementById('edit-title').value = courseTitle;
                document.getElementById('edit-slug').value = courseSlug;
                document.getElementById('edit-description').value = courseDescription;
                document.getElementById('edit-metadata').value = courseMetadata;

                // Set form action for PUT request
                editCourseForm.action = `/courses/${courseId}`;
            }
        });
    }

    // Handle Edit Course form submission (AJAX PUT request)
    if (editCourseForm) {
        editCourseForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const courseId = document.getElementById('edit-course-id').value;
            const formData = new FormData(this);
            const jsonData = {};

            // Convert FormData to JSON, excluding _method and id
            for (let [key, value] of formData.entries()) {
                if (key !== '_method' && key !== 'id') {
                    jsonData[key] = value;
                }
            }

            try {
                const response = await fetch(`/courses/${courseId}`, {
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
                alert('An error occurred while updating the course.');
            }
        });
    }

    // Handle Delete Course button click (AJAX DELETE request)
    if (courseTable) {
        courseTable.addEventListener('click', async function (event) {
            const deleteButton = event.target.closest('.btn-delete-course');
            if (deleteButton) {
                const courseId = deleteButton.dataset.courseId;
                if (confirm('Are you sure you want to delete this course?')) {
                    try {
                        const response = await fetch(`/courses/${courseId}`, {
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
                        alert('An error occurred while deleting the course.');
                    }
                }
            }
        });
    }
});
