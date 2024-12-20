// var tinyMDE = new TinyMDE.Editor({ element: "editor" });
// var tinyMDE = new TinyMDE.Editor({ textarea: "content" });
// var commandBar = new TinyMDE.CommandBar({
// element: "toolbar",
// editor: tinyMDE,
// });
document.addEventListener('DOMContentLoaded', function() {
    // Check if the slug input field exists
    var slugInput = document.getElementById('slug');
    var titleInput = document.getElementById('title');

    if (slugInput && titleInput) {
        // Add an event listener to the title input field
        titleInput.addEventListener('input', function() {
            // Generate a slug from the title
            var slug = titleInput.value
                .toLowerCase() // Convert to lowercase
                .replace(/[^a-z0-9\s-]/g, '') // Remove invalid characters
                .trim() // Trim leading/trailing whitespace
                .replace(/\s+/g, '-') // Replace spaces with hyphens
                .replace(/-+/g, '-'); // Replace multiple hyphens with a single hyphen

            // Update the slug input field
            slugInput.value = slug;
        });
    }

    // Initialize TinyMDE editor
    var tinyMDE = new TinyMDE.Editor({ textarea: "content" });
    var commandBar = new TinyMDE.CommandBar({
        element: "toolbar",
        editor: tinyMDE,
    });
});