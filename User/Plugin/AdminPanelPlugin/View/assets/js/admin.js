document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('#admin-nav a[data-section]');
    const sections = document.querySelectorAll('.admin-section');

    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();

            // Hide all sections
            sections.forEach(section => {
                section.style.display = 'none';
            });

            // Remove 'active' class from all links
            navLinks.forEach(navLink => {
                navLink.classList.remove('active');
            });

            // Show the selected section
            const sectionId = this.getAttribute('data-section');
            const activeSection = document.getElementById(sectionId);
            if (activeSection) {
                activeSection.style.display = 'block';
            }

            // Add 'active' class to the clicked link
            this.classList.add('active');
        });
    });

    // Handle 'Clear Cache' link click
    const clearCacheLink = document.getElementById('clear_cache');
    if (clearCacheLink) {
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        clearCacheLink.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default link action

            // Optional: Disable the link to prevent multiple clicks
            clearCacheLink.classList.add('disabled');
            clearCacheLink.textContent = 'Clearing Cache...';

            // Send the CSRF token in the request body
            const requestData = {
                action: 'clear_cache',
                csrf_token: csrfToken // Include CSRF token here
            };

            fetch('/admin/clearcache', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                if (response.ok) {
                    return response.json(); // Assuming the server returns JSON
                } else {
                    throw new Error('Network response was not ok');
                }
            })
            .then(data => {
                // Handle success response
                alert('Cache cleared successfully!');
            })
            .catch(error => {
                // Handle error
                console.error('There was a problem with the fetch operation:', error);
                alert('Failed to clear cache.');
            })
            .finally(() => {
                // Re-enable the link
                clearCacheLink.classList.remove('disabled');
                clearCacheLink.textContent = 'Clear Cache';
            });
        });
    }

    // Trigger click on the first nav link to display the default section
    const defaultLink = document.querySelector('#admin-nav a[data-section]');
    if (defaultLink) {
        defaultLink.click();
    }
});