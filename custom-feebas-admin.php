<?php
/*
Plugin Name: Custom Feebas Admin 
Description: Adds custom admin page, metabox, and form for custom post type
Version: 2.0
Author: Your Name
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

include_once(plugin_dir_path(__FILE__) . 'inc/ajax-camera.php');
include_once(plugin_dir_path(__FILE__) . 'inc/enqueue-scripts.php');
include_once(plugin_dir_path(__FILE__) . 'inc/dialog-ui.php');
include_once(plugin_dir_path(__FILE__) . 'inc/wp-table.php');
include_once(plugin_dir_path(__FILE__) . 'inc/headless-table.php');
include_once(plugin_dir_path(__FILE__) . 'inc/camera-list.php');

// Add Admin Menu Page
function add_custom_admin_menu()
{
    add_menu_page(
        'Custom Admin',
        'Custom Admin',
        'manage_options',
        'custom-feebas-admin',
        'render_custom_admin_page',
        'dashicons-admin-generic',
        30
    );
}
add_action('admin_menu', 'add_custom_admin_menu');

// Render Admin Page Content
function render_custom_admin_page()
{
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save form data
    if (isset($_POST['custom_form_submit'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['custom_form_nonce'], 'custom_form_action')) {
            wp_die('Invalid nonce');
        }

        // Save options
        update_option('custom_setting_1', sanitize_text_field($_POST['custom_setting_1']));
        update_option('custom_setting_2', sanitize_text_field($_POST['custom_setting_2']));

        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }

    // Get current values
    $setting1 = get_option('custom_setting_1', '');
    $setting2 = get_option('custom_setting_2', '');

    // Admin page HTML
?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form method="post" action="">
            <?php wp_nonce_field('custom_form_action', 'custom_form_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="custom_setting_1">Setting 1</label></th>
                    <td>
                        <input type="text" id="custom_setting_1" name="custom_setting_1"
                            value="<?php echo esc_attr($setting1); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="custom_setting_2">Setting 2</label></th>
                    <td>
                        <input type="text" id="custom_setting_2" name="custom_setting_2"
                            value="<?php echo esc_attr($setting2); ?>" class="regular-text">
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="custom_form_submit" class="button button-primary"
                    value="Save Settings">
            </p>
        </form>
    </div>
<?php
}

