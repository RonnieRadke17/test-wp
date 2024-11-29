<?php
// Create table on theme activation
function create_days_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'create_days_table');

// Create (Insert)
function insert_day($name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';
    
    return $wpdb->insert(
        $table_name,
        array('name' => $name),
        array('%s')
    );
}

// Read (Select)
function get_all_days() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';
    
    return $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
}

function get_day_by_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';
    
    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
    );
}

// Update
function update_day($id, $name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';
    
    return $wpdb->update(
        $table_name,
        array('name' => $name),
        array('id' => $id),
        array('%s'),
        array('%d')
    );
}

// Delete
function delete_day($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';
    
    return $wpdb->delete(
        $table_name,
        array('id' => $id),
        array('%d')
    );
}

// Add admin menu
function days_admin_menu() {
    add_menu_page(
        'Days Management',
        'Days',
        'manage_options',
        'days-management',
        'days_admin_page',
        'dashicons-calendar-alt',
        30
    );
}
add_action('admin_menu', 'days_admin_menu');

// Admin page function
function days_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';
    
    // Handle form submissions
    if (isset($_POST['submit_day'])) {
        if (isset($_POST['day_id'])) {
            update_day($_POST['day_id'], sanitize_text_field($_POST['day_name']));
            echo '<div class="notice notice-success is-dismissible"><p>Day updated successfully!</p></div>';
        } else {
            insert_day(sanitize_text_field($_POST['day_name']));
            echo '<div class="notice notice-success is-dismissible"><p>Day added successfully!</p></div>';
        }
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        delete_day($_GET['id']);
        echo '<div class="notice notice-success is-dismissible"><p>Day deleted successfully!</p></div>';
    }
    
    $editing_day = null;
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $editing_day = get_day_by_id($_GET['id']);
    }
    
    $days = get_all_days();
    
    // Include the view
    include_once get_template_directory() . '/views/days-view.php';
}