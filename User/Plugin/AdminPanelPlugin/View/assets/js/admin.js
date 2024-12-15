document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('#admin-nav a[data-section]');
    const sections = document.querySelectorAll('.admin-section');
    const loadedSections = {}; // Keep track of loaded sections

    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();

            const sectionId = this.getAttribute('data-section');
            const activeSection = document.getElementById(sectionId);
            if (!activeSection) return;

            // Hide all sections
            sections.forEach(section => {
                section.style.display = 'none';
            });

            // Remove 'active' class from all links
            navLinks.forEach(navLink => {
                navLink.classList.remove('active');
            });

            // Show the selected section
            activeSection.style.display = 'block';

            // Add 'active' class to the clicked link
            this.classList.add('active');

            // Load content dynamically if not loaded yet
            if (!loadedSections[sectionId]) {
                switch (sectionId) {
                    case 'plugins':
                        loadPlugins();
                        break;
                    // Add cases for other sections if needed
                }
                loadedSections[sectionId] = true;
            }
        });
    });

    function loadPlugins() {
        const pluginsContainer = document.getElementById('plugins-container');
        if (!pluginsContainer) return;

        // Optional: Include CSRF token if required
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        const requestData = {
            query: 'list_plugins',
            csrf_token: csrfToken
        };

        fetch('/admin/query', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(plugins => {
            // Clear loading message
            pluginsContainer.innerHTML = '';

            // Build the table
            const table = document.createElement('table');

            const thead = document.createElement('thead');
            thead.innerHTML = `
                <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>Version</th>
                    <th>Actions</th>
                </tr>
            `;
            table.appendChild(thead);

            const tbody = document.createElement('tbody');

            /*
            Sample plugin data:
                {"AdminPanelPlugin":{"active":true,"name":"Pages Plugin","version":"1.0.0"},"HomePagePlugin":{"active":false,"name":"HomePage Plugin","version":"1.0.0"},"PagesPlugin":{"active":true,"name":"Pages Plugin","version":"1.0.0"},"UserPlugin":{"active":true,"name":"User Plugin","version":"1.0.0"},"WelcomePlugin":{"active":false,"name":"Welcome Plugin","version":"1.0.0"}}
            */
            // plugins.forEach(plugin => {
            Object.keys(plugins).forEach(pluginKey => {
                const plugin = plugins[pluginKey];
                const tr = document.createElement('tr');

                const statusTd = document.createElement('td');
                statusTd.textContent = plugin.active ? 'Active' : 'Inactive';
                tr.appendChild(statusTd);

                const nameTd = document.createElement('td');
                nameTd.textContent = plugin.name;
                tr.appendChild(nameTd);

                const versionTd = document.createElement('td');
                versionTd.textContent = plugin.version;
                tr.appendChild(versionTd);

                const actionsTd = document.createElement('td');
                const editLink = document.createElement('a');
                editLink.href = `/admin/plugins/edit/${pluginKey}`;
                editLink.textContent = 'Edit';
                actionsTd.appendChild(editLink);

                const deleteLink = document.createElement('a');
                deleteLink.href = `/admin/plugins/delete/${pluginKey}`;
                deleteLink.textContent = 'Delete';
                deleteLink.style.marginLeft = '10px'; // Add some spacing
                actionsTd.appendChild(deleteLink);

                tr.appendChild(actionsTd);

                tbody.appendChild(tr);
            });

            table.appendChild(tbody);

            pluginsContainer.appendChild(table);
        })
        .catch(error => {
            console.error('Error fetching plugins:', error);
            pluginsContainer.innerHTML = '<p>Error loading plugins.</p>';
        });
    }

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

            fetch('/admin/action', {
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

    // Menu toggle for mobile
    const menuToggle = document.getElementById('menu-toggle');
    const adminNav = document.getElementById('admin-nav');
    
    if (menuToggle && adminNav) {
        menuToggle.addEventListener('click', function() {
            adminNav.classList.toggle('open');
        });

        // Add event listeners to navigation links
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                adminNav.classList.remove('open');
            });
        });
    }
    // Get references to the links and containers
    const managePluginsLink = document.getElementById('manage-plugins-link');
    const pluginStoreLink = document.getElementById('plugin-store-link');
    const pluginsContainer = document.getElementById('plugins-container');
    const storeContainer = document.getElementById('store-container');

    if (managePluginsLink && pluginStoreLink && pluginsContainer && storeContainer) {
        // Event listener for "Manage Plugins" link
        managePluginsLink.addEventListener('click', function(event) {
            event.preventDefault();

            // Show pluginsContainer and hide storeContainer
            pluginsContainer.style.display = 'block';
            storeContainer.style.display = 'none';

            // Update active link styling
            managePluginsLink.classList.add('active');
            pluginStoreLink.classList.remove('active');
        });

        // Event listener for "Plugin Store" link
        pluginStoreLink.addEventListener('click', function(event) {
            event.preventDefault();

            // Hide pluginsContainer and show storeContainer
            pluginsContainer.style.display = 'none';
            storeContainer.style.display = 'block';

            // Load store content if not loaded yet
            if (!loadedSections['pluginStore']) {
                loadPluginStore();
                loadedSections['pluginStore'] = true;
            }

            // Update active link styling
            pluginStoreLink.classList.add('active');
            managePluginsLink.classList.remove('active');
        });
    }

    // Function to load plugin store content
    function loadPluginStore() {
        // Replace this with the actual endpoint to fetch store data
        fetch('/admin/plugin-store')
            .then(response => response.text()) // Assuming the server returns HTML
            .then(html => {
                storeContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading plugin store:', error);
                storeContainer.innerHTML = '<p>Error loading plugin store.</p>';
            });
    }
});