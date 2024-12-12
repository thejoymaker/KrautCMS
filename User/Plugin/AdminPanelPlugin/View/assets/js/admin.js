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

    // Trigger click on the first nav link to display the default section
    const defaultLink = document.querySelector('#admin-nav a[data-section]');
    if (defaultLink) {
        defaultLink.click();
    }
});