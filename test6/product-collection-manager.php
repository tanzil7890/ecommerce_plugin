<?php
/*
Plugin Name: Product Collection Manager_test6
Description: Manage product collections in WordPress
Version: 1.4
Author:hfkjsd
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ProductCollectionManager {
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_sidebar_script'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_menu_page() {
        add_menu_page(
            'Product Collection Manager',
            'Product Collections',
            'manage_options',
            'pcm-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-grid-view',
            30
        );
    }

    public function render_dashboard() {
        include plugin_dir_path(__FILE__) . 'templates/dashboard.php';
    }

    public function enqueue_sidebar_script() {
        wp_enqueue_script(
            'pcm-sidebar',
            plugins_url('js/sidebar.js', __FILE__),
            array('wp-plugins', 'wp-edit-post', 'wp-element')
        );
    }

    public function enqueue_admin_scripts($hook) {
        wp_enqueue_script('pcm-admin-script', plugins_url('js/admin-script.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('pcm-collection-manager', plugins_url('js/collection-manager.js', __FILE__), array('jquery'), '1.0', true);
        
        // Enqueue the reactivation script on the dashboard page
        if ('toplevel_page_pcm-dashboard' === $hook) {
            wp_enqueue_script('pcm-reactivation', plugins_url('js/reactivation.js', __FILE__), array('jquery'), '1.0', true);
            wp_localize_script('pcm-reactivation', 'pcm_data', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pcm_reactivation_nonce'),
                'is_reactivated' => get_option('pcm_deactivated') === 'yes' ? 'true' : 'false'
            ));
        }
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style('pcm-admin-styles', plugins_url('css/admin-styles.css', __FILE__));
    }

    public function deactivate() {
        update_option('pcm_deactivated', 'yes');
    }

    public function activate() {
        // We don't need to do anything here now, as we'll check the option on the dashboard page
    }
}

new ProductCollectionManager();

// Include other necessary files
require_once plugin_dir_path(__FILE__) . 'includes/create-collection.php';
require_once plugin_dir_path(__FILE__) . 'includes/edit-collection.php';
require_once plugin_dir_path(__FILE__) . 'includes/delete-collection.php';

// Add AJAX actions
add_action('wp_ajax_pcm_restore_collections', 'pcm_ajax_restore_collections');
add_action('wp_ajax_pcm_clear_collections', 'pcm_ajax_clear_collections');

function pcm_ajax_restore_collections() {
    check_ajax_referer('pcm_reactivation_nonce', 'nonce');
    update_option('pcm_deactivated', 'no');
    wp_send_json_success(array('message' => __('Collections restored successfully.', 'pcm')));
}

function pcm_ajax_clear_collections() {
    check_ajax_referer('pcm_reactivation_nonce', 'nonce');
    delete_option('pcm_collections');
    update_option('pcm_deactivated', 'no');
    wp_send_json_success(array('message' => __('Collections cleared successfully.', 'pcm')));
}