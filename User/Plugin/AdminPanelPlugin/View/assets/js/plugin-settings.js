document.addEventListener('DOMContentLoaded', function() {
    const settingsContainer = document.getElementById('settings-container');
    const backButton = document.getElementById('back');

    if (backButton) {
        backButton.addEventListener('click', function(event) {
            event.preventDefault();
            window.history.back();
        });
    }

    if (!settingsContainer) return;

    // Extract plugin name from the URL
    const urlParts = window.location.pathname.split('/');
    const pluginName = urlParts[urlParts.length - 1];

    loadPluginSettings(pluginName);

    function loadPluginSettings(pluginName) {
        // Optional: Include CSRF token if required
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        fetch(`/admin/plugins/settings/${encodeURIComponent(pluginName)}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              // Include other headers if necessary
            },
            body: JSON.stringify({
              csrf_token: csrfToken,
              action: 'load_settings',
            }),
          })
            .then(response => response.json())
            .then(settings => {
                settingsContainer.innerHTML = '';

                if (Object.keys(settings).length === 0) {
                    settingsContainer.innerHTML = '<p>No settings found.</p>';
                    return;
                }

                // Create form element
                const form = document.createElement('form');
                form.id = 'plugin-settings-form';

                // Loop through settings and create form fields
                Object.keys(settings).forEach(key => {
                    const value = settings[key];
                    const formGroup = document.createElement('div');
                    formGroup.classList.add('form-group');

                    const label = document.createElement('label');
                    label.htmlFor = key;
                    label.textContent = key.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());

                    let input;

                    // Determine the type of the value and create appropriate input control
                    if (typeof value === 'boolean') {
                        input = document.createElement('input');
                        input.type = 'checkbox';
                        input.id = key;
                        input.name = key;
                        input.checked = value;
                    } else if (typeof value === 'number') {
                        input = document.createElement('input');
                        input.type = 'number';
                        input.id = key;
                        input.name = key;
                        input.value = value;
                        // Determine if the number is an integer or float
                        if (Number.isInteger(value)) {
                            input.step = '1';
                        } else {
                            input.step = 'any';
                        }
                    } else {
                        input = document.createElement('input');
                        input.type = 'text';
                        input.id = key;
                        input.name = key;
                        input.value = value;
                    }

                    formGroup.appendChild(label);
                    formGroup.appendChild(input);
                    form.appendChild(formGroup);
                });

                // Add CSRF token as hidden input if needed
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = 'csrf_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                }

                // Add Save button
                const saveButton = document.createElement('button');
                saveButton.type = 'submit';
                saveButton.textContent = 'Save Settings';
                form.appendChild(saveButton);

                // Append form to settings container
                settingsContainer.appendChild(form);

                // Add event listener to handle form submission
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    savePluginSettings(pluginName, new FormData(form));
                });
            })
            .catch(error => {
                console.error('Error loading plugin settings:', error);
                settingsContainer.innerHTML = '<p>Error loading plugin settings.</p>';
            });
    }

    function savePluginSettings(pluginName, formData) {
        const settings = {};
        formData.forEach((value, key) => {
            if (key !== 'csrf_token') {
                // Preserve the data type when saving settings
                if (value === 'true' || value === 'false') {
                    settings[key] = (value === 'true');
                } else if (!isNaN(value) && value.trim() !== '') {
                    settings[key] = parseFloat(value);
                } else {
                    settings[key] = value;
                }
            }
        });

        const csrfToken = formData.get('csrf_token') || '';

        const requestData = {
            action: 'save_settings',
            plugin_name: pluginName,
            settings: settings,
            csrf_token: csrfToken
        };

        fetch(`/admin/plugins/settings/${encodeURIComponent(pluginName)}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(result => {
            if (result.ok) {
                settingsContainer.innerHTML = '<p>Settings saved successfully.</p>';
                // Optionally reload the settings form
                loadPluginSettings(pluginName);
            } else {
                settingsContainer.innerHTML = '<p>Error saving settings.</p>';
            }
        })
        .catch(error => {
            console.error('Error saving plugin settings:', error);
            settingsContainer.innerHTML = '<p>Error saving settings.</p>';
        });
    }
});