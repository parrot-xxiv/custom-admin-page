<?php


// Enqueue a nonce for security when the page loads
function camera_enqueue_scripts($hook)
{
        // Exit early if not on the specific page
    if ($hook != 'toplevel_page_headless-json-table') return;
    
    // Enqueue the script located inside the /inc folder for admin
    wp_enqueue_script(
        'camera-ajax', // Handle for the script
        plugin_dir_url( __FILE__ ) . '../js/generate-posts.js', // Path to the script inside /inc folder
        array( 'jquery' ),  // Dependencies (optional, like jQuery if you are using it)
        null,  // Version (can set a version number or null)
        true   // Load in footer (recommended for performance)
    );
    wp_localize_script('camera-ajax', 'cameraAjax', array(
        'nonce' => wp_create_nonce('create_camera_post_nonce') // Nonce for security
    ));
}
add_action('admin_enqueue_scripts', 'camera_enqueue_scripts');

// AJAX handler to create posts
function create_camera_post()
{
    // Check nonce for security (optional but recommended)
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_camera_post_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
        return;
    }

    // Get the payload (array of camera data)
    $camera_data = isset($_POST['camera_data']) ? $_POST['camera_data'] : [];

    // Loop through the data and create the camera posts
    foreach ($camera_data as $data) {
        $post_data = array(
            'post_title'   => sanitize_text_field($data['title']),
            'post_content' => sanitize_textarea_field($data['content']),
            'post_type'    => 'camera',  // Custom post type
            'post_status'  => 'publish', // Or 'draft', 'pending', etc.
        );

        // Insert the post
        $post_id = wp_insert_post($post_data);

        // You can also handle custom fields or taxonomies here, if needed
        if ($post_id) {
            // Example: Add a custom field
            update_post_meta($post_id, '_camera_featured', sanitize_text_field($data['featured']));
        }
    }

    // Respond with success
    wp_send_json_success(array('message' => 'Posts created successfully.'));
}
add_action('wp_ajax_create_camera_post', 'create_camera_post'); // Logged-in users
// add_action('wp_ajax_nopriv_create_camera_post', 'create_camera_post'); // For non-logged-in users (if needed)
