<?php


function my_admin_enqueue_scripts($hook) {
    // Enqueue the WordPress media scripts and styles.
    
    if ($hook != 'toplevel_page_dialog-ui-page') return;
    wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_style( 'wp-jquery-ui-dialog' ); // if available or a custom jQuery UI theme CSS

    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'my_admin_enqueue_scripts' );

function dialog_ui_page() {
    include plugin_dir_path( __FILE__ ) . 'dialog-ui-template.php'; 
}

function dui_add_admin_menu() {
    add_menu_page(
        __( 'Dialog UI Page', 'custom-feebas-admin' ),
        __( 'Dialog UI', 'custom-feebas-admin' ),
        'manage_options',
        'dialog-ui-page',
        'dialog_ui_page',
        'dashicons-editor-table',
        6
    );
}
add_action( 'admin_menu', 'dui_add_admin_menu' );