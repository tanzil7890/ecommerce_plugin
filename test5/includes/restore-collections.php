<?php
add_action('admin_menu', 'pcm_add_restore_collections_page');

function pcm_add_restore_collections_page() {
    add_submenu_page(
        null,
        'Restore Collections',
        'Restore Collections',
        'manage_options',
        'pcm-restore-collections',
        'pcm_restore_collections_page'
    );
}

function pcm_restore_collections_page() {
    // The collections are already in the database, we just need to acknowledge the restoration
    update_option('pcm_deactivated', 'no');
    
    // Redirect to the dashboard with a success message
    wp_redirect(admin_url('admin.php?page=pcm-dashboard&restored=1'));
    exit;
}

// Add a notice for successful restoration
add_action('admin_notices', 'pcm_restoration_notice');

function pcm_restoration_notice() {
    if (isset($_GET['page']) && $_GET['page'] === 'pcm-dashboard' && isset($_GET['restored'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Your collections have been successfully restored.', 'pcm'); ?></p>
        </div>
        <?php
    }
}