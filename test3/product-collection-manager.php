<?php
/*
Plugin Name: Product Collection Manager_test3
Description: Manage product collections in WordPress
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ProductCollectionManager {
    public function __construct() {
        add_action('init', array($this, 'init'));
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_sidebar_script'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles')); // Add this line here
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
        if ('toplevel_page_pcm-dashboard' !== $hook) {
            return;
        }
        wp_enqueue_script('pcm-admin-script', plugins_url('js/admin-script.js', __FILE__), array('jquery'), '1.0', true);
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style('pcm-admin-styles', plugins_url('css/admin-styles.css', __FILE__));
    }
}

new ProductCollectionManager();

// Include other necessary files
require_once plugin_dir_path(__FILE__) . 'includes/create-collection.php';
require_once plugin_dir_path(__FILE__) . 'includes/edit-collection.php';
require_once plugin_dir_path(__FILE__) . 'includes/delete-collection.php';