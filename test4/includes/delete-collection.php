<?php
add_action('wp_ajax_pcm_delete_collection', 'pcm_delete_collection');

function pcm_delete_collection() {
    check_ajax_referer('pcm_delete_collection', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $collection_name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';

    if (empty($collection_name)) {
        wp_send_json_error('Collection name is required.');
    }

    $collections = get_option('pcm_collections', array());

    if (isset($collections[$collection_name])) {
        unset($collections[$collection_name]);
        update_option('pcm_collections', $collections);
        wp_send_json_success('Collection deleted successfully.');
    } else {
        wp_send_json_error('Collection not found.');
    }
}