<div>
    <button id="add-images-gallery" class="button">Add Images to Gallery</button>
    <div id="gallery-preview" style="margin-top:10px;"></div>

    <!-- Dialog content (initially hidden) -->
    <div id="form-dialog" title="Sample Form Dialog" style="display:none;">
        <form id="sample-form">
            <fieldset>
                <label for="user-name">Name:</label>
                <input type="text" name="user-name" id="user-name" class="text ui-widget-content ui-corner-all" required>

                <label for="user-email">Email:</label>
                <input type="email" name="user-email" id="user-email" class="text ui-widget-content ui-corner-all" required>

                <!-- A hidden submit button to allow form submission via Enter key -->
                <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
            </fieldset>
        </form>
    </div>

    <!-- Button to open the dialog -->
    <button id="open-dialog" class="button">Open Form Dialog</button>

</div>
<script>
    
    jQuery(document).ready(function($) {
        // Initialize the dialog with options.
        var dialog = $("#form-dialog").dialog({
            autoOpen: false, // Keep the dialog closed initially
            modal: true, // Modal dialog disables interaction with the background
            width: 400,
            buttons: {
                "Submit": function() {
                    // Retrieve the form field values
                    var name = $("#user-name").val();
                    var email = $("#user-email").val();

                    // Do something with the form data (for example, alert it)
                    alert("Name: " + name + "\nEmail: " + email);

                    // Close the dialog after submission
                    $(this).dialog("close");
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            },
            close: function() {
                // Optional: reset the form when the dialog is closed.
                $("#sample-form")[0].reset();
            }
        });

        // Prevent the form from submitting normally.
        $("#sample-form").on("submit", function(event) {
            event.preventDefault();
        });

        // Set up the button to open the dialog.
        $("#open-dialog").on("click", function() {
            dialog.dialog("open");
        });
    });

    jQuery(document).ready(function($) {
        var galleryFrame;

        $('#add-images-gallery').on('click', function(e) {
            e.preventDefault();

            // If the media frame already exists, reopen it.
            if (galleryFrame) {
                galleryFrame.open();
                return;
            }

            // Create a new media frame with multiple selection enabled.
            galleryFrame = wp.media({
                title: 'Select Images for Gallery',
                button: {
                    text: 'Add to Gallery'
                },
                multiple: true // Enable multiple file selection.
            });

            // When images are selected, run a callback.
            galleryFrame.on('select', function() {
                var selection = galleryFrame.state().get('selection');
                $('#gallery-preview').empty(); // Clear any previous images.

                // Iterate over each selected attachment.
                selection.each(function(attachment) {
                    attachment = attachment.toJSON();
                    // Append a thumbnail image into the preview container.
                    $('#gallery-preview').append(
                        '<img src="' + attachment.sizes.thumbnail.url + '" style="margin:5px; border:1px solid #ccc;" />'
                    );
                });
            });

            // Open the modal.
            galleryFrame.open();
        });
    });
</script>