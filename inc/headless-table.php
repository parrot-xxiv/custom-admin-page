
<?php

/**
 * AJAX callback to return JSON table data with search and pagination.
 */
function hjt_get_table_data()
{
    if (! current_user_can('manage_options')) {
        wp_send_json_error();
    }

    // Get query parameters.
    $paged  = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

    // Sample data (20 items).
    $data = [
        ['id' => 1,  'name' => 'John Doe',           'email' => 'john@example.com'],
        ['id' => 2,  'name' => 'Jane Smith',         'email' => 'jane@example.com'],
        ['id' => 3,  'name' => 'Bob Johnson',        'email' => 'bob@example.com'],
        ['id' => 4,  'name' => 'Alice Williams',     'email' => 'alice@example.com'],
        ['id' => 5,  'name' => 'Michael Brown',      'email' => 'michael@example.com'],
        ['id' => 6,  'name' => 'Lisa Davis',         'email' => 'lisa@example.com'],
        ['id' => 7,  'name' => 'Tom Wilson',         'email' => 'tom@example.com'],
        ['id' => 8,  'name' => 'Sara Lee',           'email' => 'sara@example.com'],
        ['id' => 9,  'name' => 'David Kim',          'email' => 'david@example.com'],
        ['id' => 10, 'name' => 'Emma Garcia',        'email' => 'emma@example.com'],
        ['id' => 11, 'name' => 'Oliver Martinez',    'email' => 'oliver@example.com'],
        ['id' => 12, 'name' => 'Sophia Hernandez',   'email' => 'sophia@example.com'],
        ['id' => 13, 'name' => 'Liam Anderson',      'email' => 'liam@example.com'],
        ['id' => 14, 'name' => 'Mia Thomas',         'email' => 'mia@example.com'],
        ['id' => 15, 'name' => 'Noah Taylor',        'email' => 'noah@example.com'],
        ['id' => 16, 'name' => 'Ava Moore',          'email' => 'ava@example.com'],
        ['id' => 17, 'name' => 'William Jackson',    'email' => 'william@example.com'],
        ['id' => 18, 'name' => 'Isabella Martin',    'email' => 'isabella@example.com'],
        ['id' => 19, 'name' => 'James Lee',          'email' => 'james@example.com'],
        ['id' => 20, 'name' => 'Charlotte Perez',    'email' => 'charlotte@example.com'],
    ];

    // Filter data if a search term is provided.
    if (! empty($search)) {
        $data = array_filter($data, function ($item) use ($search) {
            return (false !== stripos($item['name'], $search) || false !== stripos($item['email'], $search));
        });
        $data = array_values($data); // re-index array
    }

    $total_items = count($data);
    $per_page    = 5;
    $total_pages = ceil($total_items / $per_page);

    // Slice the data for the current page.
    $offset     = ($paged - 1) * $per_page;
    $paged_data = array_slice($data, $offset, $per_page);

    $result = [
        'items'        => $paged_data,
        'total_items'  => $total_items,
        'per_page'     => $per_page,
        'total_pages'  => $total_pages,
        'current_page' => $paged,
    ];

    wp_send_json_success($result);
}
add_action('wp_ajax_hjt_get_table_data', 'hjt_get_table_data');

/**
 * Render the admin page.
 */
function hjt_admin_page()
{
    include plugin_dir_path(__FILE__) . 'admin-table-template.php';
}

/**
 * Add the admin menu page.
 */
function hjt_add_admin_menu()
{
    add_menu_page(
        __('Headless JSON Table', 'headless-json-table'),
        __('Headless Table', 'headless-json-table'),
        'manage_options',
        'headless-json-table',
        'hjt_admin_page',
        'dashicons-editor-table',
        6
    );
}
add_action('admin_menu', 'hjt_add_admin_menu');
