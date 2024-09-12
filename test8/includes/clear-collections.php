<?php
add_action('admin_menu', 'pcm_add_clear_collections_page');

function pcm_add_clear_collections_page() {
    add_submenu_page(
        null,
        'Clear Collections',
        'Clear Collections',
        'manage_options',
        'pcm-clear-collections',
        'pcm_clear_collections_page'
    );
}

function pcm_clear_collections_page() {
    // Clear the collections
    delete_option('pcm_collections');
    update_option('pcm_deactivated', 'no');
    
    // Redirect to the dashboard with a success message
    wp_redirect(admin_url('admin.php?page=pcm-dashboard&cleared=1'));
    exit;
}

// Add a notice for successful clearing
add_action('admin_notices', 'pcm_clearing_notice');

function pcm_clearing_notice() {
    if (isset($_GET['page']) && $_GET['page'] === 'pcm-dashboard' && isset($_GET['cleared'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Your collections have been cleared. You can start fresh now.', 'pcm'); ?></p>
        </div>
        <?php
    }
}