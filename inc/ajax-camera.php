<?php


// // Enqueue a nonce for security when the page loads
// function camera_enqueue_scripts($hook)
// {
//     // Exit early if not on the specific page
//     if ($hook != 'toplevel_page_camera-list') return;

//     // Enqueue the script located inside the /inc folder for admin
//     wp_enqueue_script(
//         'camera-ajax', // Handle for the script
//         plugin_dir_url(__FILE__) . '../js/generate-posts.js', // Path to the script inside /inc folder
//         array('jquery'),  // Dependencies (optional, like jQuery if you are using it)
//         null,  // Version (can set a version number or null)
//         true   // Load in footer (recommended for performance)
//     );
//     wp_localize_script('camera-ajax', 'cameraAjax', array(
//         'nonce' => wp_create_nonce('create_camera_post_nonce') // Nonce for security
//     ));
// }
// add_action('admin_enqueue_scripts', 'camera_enqueue_scripts');

// AJAX handler to create a single camera post
function create_camera_post()
{
    // Check nonce for security (optional but recommended)
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_camera_post_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.', 'angnonce' => $_POST['nonce']));
        return;
    }

    // Get the single camera data from the request
    $camera_data = isset($_POST['camera_data']) ? json_decode(stripslashes($_POST['camera_data']), true) : null;

    // If no camera data is provided, return an error
    if (!$camera_data) {
        wp_send_json_error(array('message' => 'No camera data provided.'));
        return;
    }

    // Prepare the data for the camera post
    $post_data = array(
        'post_title'   => isset($camera_data['title']) ? sanitize_text_field($camera_data['title']) : '',
        'post_type'    => 'camera',  // Custom post type
        'post_status'  => 'publish', // Or 'draft', 'pending', etc.
    );

    // Insert the post
    $post_id = wp_insert_post($post_data);

    // Check if post was successfully inserted
    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Failed to create post.'));
        return;
    }

    // Handle custom meta and taxonomy fields
    if (isset($camera_data['description']) && !empty($camera_data['description'])) {
        update_post_meta($post_id, 'description', sanitize_text_field($camera_data['description']));
    }

    if (isset($camera_data['price']) && !empty($camera_data['price'])) {
        update_post_meta($post_id, 'price', sanitize_text_field($camera_data['price']));
    }

    // Example: Add a custom field (price)
    // update_post_meta($post_id, 'price', rand(3000, 20000));

    if (isset($camera_data['brand']) && !empty($camera_data['brand'])) {
        wp_set_object_terms($post_id, sanitize_text_field($camera_data['brand']), 'brand');
    }

    if (isset($camera_data['type']) && !empty($camera_data['type'])) {
        wp_set_object_terms($post_id, sanitize_text_field($camera_data['type']), 'type');
    }

    // Set the featured image if an image attachment ID was provided
    if (isset($camera_data['image']) && !empty($camera_data['image'])) {
        $image_id = intval($camera_data['image']);
        set_post_thumbnail($post_id, $image_id);
        update_post_meta($post_id, 'image_url', sanitize_text_field($camera_data['imageUrl']));
    }

    // Respond with success
    wp_send_json_success(array('message' => 'Post created successfully.', 'payload' => $camera_data));
}
add_action('wp_ajax_create_camera_post', 'create_camera_post'); // Logged-in users
// add_action('wp_ajax_nopriv_create_camera_post', 'create_camera_post'); // For non-logged-in users (if needed)


function delete_all_camera()
{
    // Check for proper permission (optional, depending on how you want to handle access)
    if (!current_user_can('administrator')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
    }

    // Example: Query all posts of a custom post type 'camera' and delete them
    $args = array(
        'post_type' => 'camera',
        'posts_per_page' => -1, // Retrieve all posts
        'post_status' => 'any', // Include posts with any status
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            wp_delete_post(get_the_ID(), true); // true will force deletion (skip trash)
        }
        wp_send_json_success(array('message' => 'All cameras deleted successfully'));
    } else {
        wp_send_json_error(array('message' => 'No cameras found'));
    }

    wp_die(); // Always call wp_die() after handling an AJAX request
}

// Hook for logged-in users
add_action('wp_ajax_delete_all_camera', 'delete_all_camera');

// Hook for logged-out users (if you want it to work for non-logged-in users too)
// add_action('wp_ajax_nopriv_delete_all_camera', 'delete_all_camera');


function get_camera_list() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }

    // Get query parameters.
    $paged  = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
    $search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

    // Set the number of items per page.
    $per_page = 5;

    // Build the query arguments.
    $args = [
        'post_type'      => 'camera',        // Change to your custom post type slug.
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
    ];

    // If a search term is provided, add it to the query.
    if ( ! empty( $search ) ) {
        // Note: The built-in 's' parameter searches in post title and content.
        // If you need to search a custom meta (like email), you could also add a meta_query.
        $args['s'] = $search;
    }

    // Execute the query.
    $query = new WP_Query( $args );
    $data  = [];

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $description = get_post_meta( get_the_ID(), 'description', true );
            $price = get_post_meta( get_the_ID(), 'price', true );
            $image_url = get_post_meta( get_the_ID(), 'image_url', true );

            // Build your row data. For example, using post ID, title and a meta field for email.
            $data[] = [
                'id'    => get_the_ID(),
                'title'  => get_the_title(),
                // 'description' => get_the_content()
                'description' => $description,
                'price' => $price, 
                'brand' => wp_get_post_terms(get_the_ID(), 'brand'),
                'type' => wp_get_post_terms(get_the_ID(), 'type'),
                'image_url' => $image_url 
            ];
        }
    }
    wp_reset_postdata();

    // Get total items and pages from the query.
    $total_items = intval( $query->found_posts );
    $total_pages = intval( $query->max_num_pages );

    $result = [
        'items'        => $data,
        'total_items'  => $total_items,
        'per_page'     => $per_page,
        'total_pages'  => $total_pages,
        'current_page' => $paged,
    ];

    wp_send_json_success( $result );
}
add_action( 'wp_ajax_get_camera_list', 'get_camera_list' );
