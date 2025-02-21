<?php

function for_dialog_page($hook)
{
    // Enqueue the WordPress media scripts and styles.
    if ($hook != 'toplevel_page_dialog-ui-page') return;
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog'); // if available or a custom jQuery UI theme CSS
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'for_dialog_page');

function for_camera_list_page($hook)
{
    // Enqueue the WordPress media scripts and styles.
    if ($hook != 'toplevel_page_camera-list') return;
    wp_enqueue_script(
        'alpine-js',
        'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js',
        array(), // No dependencies
        null, // No version specified
        false // Load in footer
    );

    // Enqueue the wp.media script for the media uploader
    wp_enqueue_media();

    // Enqueue the custom script.js file that depends on Alpine.js and wp_media
    wp_enqueue_script(
        'camera-list',
        plugin_dir_url(__FILE__) . '../js/camera-list.js',
        array('alpine-js'), // Specify dependencies
        null, // Version number (optional)
        true // Load in footer
    );

    wp_localize_script('camera-list', 'cameraAjax', array(
        'nonce' => wp_create_nonce('create_camera_post_nonce') // Nonce for security
    ));
}
add_action('admin_enqueue_scripts', 'for_camera_list_page');

// Add 'defer' attribute to the script
function add_defer_attribute($tag, $handle)
{
    // Check for the specific script handle
    if ('alpine-js' === $handle) {
        // Add the 'defer' attribute
        return str_replace('src', 'defer src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);
