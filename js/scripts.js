document.addEventListener('DOMContentLoaded', () => {
    // Display confirmation before deleting a page
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', event => {
            if (!confirm('Are you sure you want to delete this page?')) {
                event.preventDefault();
            }
        });
    });
});
