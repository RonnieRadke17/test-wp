<?php
// Add this as a new file: days-admin-page.php

function days_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';
    
    // Handle form submissions
    if (isset($_POST['submit_day'])) {
        if (isset($_POST['day_id'])) {
            // Update
            update_day($_POST['day_id'], sanitize_text_field($_POST['day_name']));
        } else {
            // Insert
            insert_day(sanitize_text_field($_POST['day_name']));
        }
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        delete_day($_GET['id']);
    }
    
    // Get day for editing if ID is provided
    $editing_day = null;
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $editing_day = get_day_by_id($_GET['id']);
    }
    
    // Get all days
    $days = get_all_days();
    ?>
    
    <div class="wrap">
        <h1><?php echo $editing_day ? 'Edit Day' : 'Add New Day'; ?></h1>
        
        <form method="post" action="">
            <?php if ($editing_day): ?>
                <input type="hidden" name="day_id" value="<?php echo $editing_day->id; ?>">
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="day_name">Name</label></th>
                    <td>
                        <input type="text" name="day_name" id="day_name" class="regular-text" 
                               value="<?php echo $editing_day ? esc_attr($editing_day->name) : ''; ?>" required>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Save Day', 'primary', 'submit_day'); ?>
        </form>
        
        <h2>All Days</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($days as $day): ?>
                    <tr>
                        <td><?php echo $day->id; ?></td>
                        <td><?php echo esc_html($day->name); ?></td>
                        <td><?php echo $day->created_at; ?></td>
                        <td>
                            <a href="?page=days-management&action=edit&id=<?php echo $day->id; ?>" 
                               class="button button-small">Edit</a>
                            <a href="?page=days-management&action=delete&id=<?php echo $day->id; ?>" 
                               class="button button-small" 
                               onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}