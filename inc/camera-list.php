
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

