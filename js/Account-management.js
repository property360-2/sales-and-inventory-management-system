    // Wait for the document to be ready
document.addEventListener('DOMContentLoaded', function () {
    // Get all delete buttons
    const deleteButtons = document.querySelectorAll('.btn-danger');

    // Add click event listener to each delete button
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            // Prevent the default action (i.e., navigating to the delete URL)
            event.preventDefault();

            // Show a confirmation dialog
            const userConfirmation = confirm('Are you sure you want to delete this user? This action cannot be undone.');

            // If the user confirms, redirect to the delete URL
            if (userConfirmation) {
                window.location.href = button.href;
            }
        });
    });
});
