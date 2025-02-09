<?php
/*
Plugin Name: Custom Feebas Admin 
Description: Adds custom admin page, metabox, and form for custom post type
Version: 2.0
Author: Your Name
*/

include_once(plugin_dir_path(__FILE__) . 'inc/headless-table.php');


// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

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


// Include WP_List_Table if not already loaded.
if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Custom_JSON_Table class.
 */
class Custom_JSON_Table extends WP_List_Table
{

    /**
     * Data to display.
     *
     * @var array
     */
    protected $items_data = [];

    /**
     * Constructor.
     *
     * @param array $data Array of data items.
     */
    public function __construct($data)
    {
        parent::__construct([
            'singular' => __('Item', 'custom-json-table'),
            'plural'   => __('Items', 'custom-json-table'),
            'ajax'     => false, // We’re handling ajax externally.
        ]);
        $this->items_data = $data;
    }

    /**
     * Define the columns.
     *
     * @return array
     */
    public function get_columns()
    {
        return [
            'cb'    => '<input type="checkbox" />',
            'id'    => __('ID', 'custom-json-table'),
            'name'  => __('Name', 'custom-json-table'),
            'email' => __('Email', 'custom-json-table'),
        ];
    }

    /**
     * Render the checkbox column.
     *
     * @param array $item Current item.
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />',
            esc_attr($item['id'])
        );
    }

    /**
     * Default column rendering.
     *
     * @param array  $item        Current item.
     * @param string $column_name Column name.
     * @return string
     */
    public function column_default($item, $column_name)
    {
        if (in_array($column_name, ['id', 'name', 'email'], true)) {
            return esc_html($item[$column_name]);
        }
        return print_r($item, true);
    }

    /**
     * Prepare the items (filter, paginate, etc.).
     */
    public function prepare_items()
    {
        $per_page     = 10;
        $current_page = $this->get_pagenum();
        $search       = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';

        // Filter data based on search query.
        $data = $this->items_data;
        if (! empty($search)) {
            $data = array_filter($data, function ($item) use ($search) {
                return (false !== stripos($item['name'], $search) || false !== stripos($item['email'], $search));
            });
        }

        $total_items = count($data);
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        $this->items = $data;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /**
     * (Optional) Define bulk actions.
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        return [
            'delete' => __('Delete', 'custom-json-table'),
        ];
    }
}

/**
 * AJAX callback to output the table HTML.
 */
function load_custom_json_table_callback()
{
    // Retrieve pagination and search parameters.
    $page   = isset($_REQUEST['paged']) ? intval($_REQUEST['paged']) : 1;
    $search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';

    // Sample custom JSON data. In a real scenario, this could be from a file or API.
    $json_data = '[
        {"id": 1, "name": "John Doe", "email": "john@example.com"},
        {"id": 2, "name": "Jane Smith", "email": "jane@example.com"},
        {"id": 3, "name": "Bob Johnson", "email": "bob@example.com"},
        {"id": 4, "name": "Alice Williams", "email": "alice@example.com"},
        {"id": 5, "name": "Michael Brown", "email": "michael@example.com"},
        {"id": 6, "name": "Lisa Davis", "email": "lisa@example.com"},
        {"id": 7, "name": "Tom Wilson", "email": "tom@example.com"},
        {"id": 8, "name": "Sara Lee", "email": "sara@example.com"},
        {"id": 9, "name": "David Kim", "email": "david@example.com"},
        {"id": 10, "name": "Emma Garcia", "email": "emma@example.com"},
        {"id": 11, "name": "Oliver Martinez", "email": "oliver@example.com"}
    ]';
    $data = json_decode($json_data, true);
    if (! is_array($data)) {
        $data = [];
    }

    // Ensure our pagination parameters are in $_REQUEST.
    $_REQUEST['paged'] = $page;
    $_REQUEST['s']     = $search;

    // Instantiate and prepare our table.
    $table = new Custom_JSON_Table($data);
    ob_start();
    $table->prepare_items();
    echo '<form method="post" id="custom-json-table-form">';
    $table->display();
    echo '</form>';
    $html = ob_get_clean();
    echo $html;
    wp_die();
}
add_action('wp_ajax_load_custom_json_table', 'load_custom_json_table_callback');

/**
 * Render the admin page.
 */
function custom_json_table_page()
{
?>
    <div class="wrap">
        <h1><?php esc_html_e('Custom JSON Table', 'custom-json-table'); ?></h1>
        <!-- Search form -->
        <form id="custom-json-table-search-form">
            <input type="text" name="s" id="custom-json-table-search" placeholder="<?php esc_attr_e('Search', 'custom-json-table'); ?>" />
            <button type="submit" class="button"><?php esc_html_e('Search', 'custom-json-table'); ?></button>
        </form>
        <!-- Container where table loads via AJAX -->
        <div id="custom-json-table-container"></div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Function to load the table via AJAX.
            function loadTable(page, search) {
                page = page || 1;
                search = search || '';
                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    data: {
                        action: 'load_custom_json_table',
                        paged: page,
                        s: search
                    },
                    beforeSend: function() {
                        $('#custom-json-table-container').html('<p><?php esc_html_e('Loading...', 'custom-json-table'); ?></p>');
                    },
                    success: function(response) {
                        $('#custom-json-table-container').html(response);
                    }
                });
            }
            // Load the table on page load.
            loadTable();

            // Handle search form submission.
            $('#custom-json-table-search-form').on('submit', function(e) {
                e.preventDefault();
                var search = $('#custom-json-table-search').val();
                loadTable(1, search);
            });

            // Delegate click event on pagination links.
            $('#custom-json-table-container').on('click', '.tablenav-pages a', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                // Parse the 'paged' parameter from the link.
                var params = new URLSearchParams(href.split('?')[1]);
                var paged = params.get('paged') || 1;
                var search = $('#custom-json-table-search').val();
                loadTable(paged, search);
            });
        });
    </script>
<?php
}

/**
 * Add our admin page to the menu.
 */
function custom_json_table_menu()
{
    add_menu_page(
        __('Custom JSON Table', 'custom-json-table'),
        __('JSON Table', 'custom-json-table'),
        'manage_options',
        'custom-json-table',
        'custom_json_table_page',
        'dashicons-list-view',
        6
    );
}
add_action('admin_menu', 'custom_json_table_menu');
