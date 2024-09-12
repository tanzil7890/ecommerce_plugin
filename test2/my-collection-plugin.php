<?php
/*
Plugin Name: My Collection Plugin12
Description: A plugin to create and manage collections of products.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class MyCollectionPlugin {
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_sidebar_script'));
        add_action('wp_ajax_delete_collection', array($this, 'ajax_delete_collection'));
        add_action('wp_ajax_edit_collection', array($this, 'ajax_edit_collection'));
}

    public function ajax_edit_collection() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $collection_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        wp_redirect(admin_url('admin.php?page=edit-collection&id=' . $collection_id));
        exit;
    }

    public function ajax_delete_collection() {
        check_ajax_referer('my_collection_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']) : 0;

        if (!$collection_id) {
            wp_send_json_error('Invalid collection ID');
        }

        $result = wp_delete_post($collection_id, true);

        if ($result) {
            wp_send_json_success('Collection deleted successfully');
        } else {
            wp_send_json_error('Failed to delete collection');
        }
    }

    public function init() {
        // Register custom post type for collections
        register_post_type('collection', array(
            'public' => true,
            'label' => 'Collections'
        ));
    }

    public function add_admin_menu() {
        add_menu_page(
            'My Collections',
            'My Collections',
            'manage_options',
            'my-collections',
            array($this, 'render_dashboard'),
            'dashicons-grid-view',
            30
        );
    
        // Add submenu page for creating new collection
        add_submenu_page(
            'my-collections',
            'Create New Collection',
            'Create New Collection',
            'manage_options',
            'create-new-collection',
            array($this, 'render_create_collection')
        );
    
        // Add submenu page for editing collection
        add_submenu_page(
            null,
            'Edit Collection',
            'Edit Collection',
            'manage_options',
            'edit-collection',
            array($this, 'render_edit_collection')
        );
    }
    
    public function render_edit_collection() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
    
        $collection_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $collection = get_post($collection_id);
    
        if (!$collection || $collection->post_type !== 'collection') {
            wp_die('Collection not found');
        }
    
        include plugin_dir_path(__FILE__) . 'admin/edit-collection.php';
    }
    

    public function render_dashboard() {
        include plugin_dir_path(__FILE__) . 'admin/dashboard.php';
    }
    public function render_create_collection() {
        include plugin_dir_path(__FILE__) . 'admin/create-collection.php';
    }
    


    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_my-collections' !== $hook) {
            return;
        }
        wp_enqueue_style('my-collection-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
        wp_enqueue_script('my-collection-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), null, true);
        
        wp_localize_script('my-collection-admin-script', 'myCollectionPlugin', array(
            'nonce' => wp_create_nonce('my_collection_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    public function enqueue_sidebar_script() {
        wp_enqueue_script(
            'my-collection-sidebar',
            plugin_dir_url(__FILE__) . 'assets/js/sidebar.js',
            array('wp-plugins', 'wp-edit-post', 'wp-element')
        );
    }
}

new MyCollectionPlugin();