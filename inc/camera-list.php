
<?php 

/**
 * Render the admin page.
 */
function camera_list_page() {
    include plugin_dir_path( __FILE__ ) . 'camera-list-template.php'; 
}

/**
 * Add the admin menu page.
 */
function camlist_add_admin_menu() {
    add_menu_page(
        __( 'Camera List', 'camera-list' ), // Page Title
        __( 'Camera List', 'camera-list' ), // Menu Title
        'manage_options',
        'camera-list', // slug
        'camera_list_page', // page template
        'dashicons-editor-table',
        6
    );
}
add_action( 'admin_menu', 'camlist_add_admin_menu' );

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

            // Build your row data. For example, using post ID, title and a meta field for email.
            $data[] = [
                'id'    => get_the_ID(),
                'title'  => get_the_title(),
                // 'description' => get_the_content()
                'description' => $description,
                'price' => $price, 
                'brand' => wp_get_post_terms(get_the_ID(), 'brand'),
                'type' => wp_get_post_terms(get_the_ID(), 'type')
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
