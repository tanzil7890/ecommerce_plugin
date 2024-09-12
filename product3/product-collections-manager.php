<?php
/*
Plugin Name: Works
Description: Create and manage product collections 
Version: 1.4
Author: Tanzil1
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Add menu item to the sidebar
function pcm_menu() {
    add_menu_page(
        'Product Collections',
        'Product Collections',
        'manage_options',
        'product-collections',
        'pcm_dashboard_page',
        'dashicons-portfolio',
        30
    );
    add_submenu_page(
        'product-collections',
        'Create New Collection',
        'Create New Collection',
        'manage_options',
        'create-collection',
        'pcm_create_collection_page'
    );
    add_submenu_page(
        null,
        'Edit Collection',
        'Edit Collection',
        'manage_options',
        'edit-collection',
        'pcm_edit_collection_page'
    );
}
add_action('admin_menu', 'pcm_menu');

// Dashboard page
function pcm_dashboard_page() {
    ?>
    <div class="wrap">
        <h1>Product Collections</h1>
        <h2>Created Collections</h2>
        <?php
        $collections = get_option('pcm_collections', array());
        if (!empty($collections)) {
            echo '<div class="pcm-collection-list">';
            foreach ($collections as $name => $products) {
                $edit_url = admin_url('admin.php?page=edit-collection&collection=' . urlencode($name));
                echo '<div class="pcm-collection-box">';
                echo '<a href="' . esc_url($edit_url) . '">';
                echo '<h3>' . esc_html($name) . '</h3>';
                echo '<p>' . count($products) . ' products</p>';
                echo '</a>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>No collections created yet.</p>';
        }
        ?>
        <a href="<?php echo admin_url('admin.php?page=create-collection'); ?>" class="button button-primary">Create New Collection</a>
    </div>
    <?php
}

// Create collection page
function pcm_create_collection_page() {
    if (isset($_POST['pcm_create_collection'])) {
        $collection_name = sanitize_text_field($_POST['collection_name']);
        $selected_products = isset($_POST['selected_products']) ? array_map('intval', $_POST['selected_products']) : array();
        
        $collections = get_option('pcm_collections', array());
        $collections[$collection_name] = $selected_products;
        update_option('pcm_collections', $collections);
        
        wp_redirect(admin_url('admin.php?page=product-collections&message=created'));
        exit;
    }
    
    ?>
    <div class="wrap">
        <h1>Create New Collection</h1>
        <form method="post" action="" id="pcm-create-collection-form">
            <label for="collection_name">Collection Name:</label>
            <input type="text" id="collection_name" name="collection_name" required>
            
            <h2>Products</h2>
            <div id="product-list">
                <?php
                $products = wc_get_products(array('limit' => -1));
                foreach ($products as $product) {
                    ?>
                    <div class="product-item">
                        <label>
                            <input type="checkbox" name="selected_products[]" value="<?php echo $product->get_id(); ?>">
                            <?php echo $product->get_name(); ?>
                        </label>
                    </div>
                    <?php
                }
                ?>
            </div>
            <p class="submit">
                <input type="submit" name="pcm_create_collection" class="button button-primary" value="Save Collection">
            </p>
        </form>
    </div>
    <?php
}

// Edit collection page
function pcm_edit_collection_page() {
    if (!isset($_GET['collection'])) {
        wp_redirect(admin_url('admin.php?page=product-collections'));
        exit;
    }

    $collection_name = urldecode($_GET['collection']);
    $collections = get_option('pcm_collections', array());

    if (!isset($collections[$collection_name])) {
        wp_redirect(admin_url('admin.php?page=product-collections'));
        exit;
    }

    $selected_products = $collections[$collection_name];

    if (isset($_POST['pcm_edit_collection'])) {
        $new_collection_name = sanitize_text_field($_POST['collection_name']);
        $new_selected_products = isset($_POST['selected_products']) ? array_map('intval', $_POST['selected_products']) : array();

        unset($collections[$collection_name]);
        $collections[$new_collection_name] = $new_selected_products;
        update_option('pcm_collections', $collections);

        wp_redirect(admin_url('admin.php?page=product-collections&message=updated'));
        exit;
    }

    ?>
    <div class="wrap">
        <h1>Edit Collection</h1>
        <form method="post" action="" id="pcm-edit-collection-form">
            <label for="collection_name">Collection Name:</label>
            <input type="text" id="collection_name" name="collection_name" value="<?php echo esc_attr($collection_name); ?>" required>
            
            <h2>Products</h2>
            <div id="product-list">
                <?php
                $products = wc_get_products(array('limit' => -1));
                foreach ($products as $product) {
                    ?>
                    <div class="product-item">
                        <label>
                            <input type="checkbox" name="selected_products[]" value="<?php echo $product->get_id(); ?>" <?php checked(in_array($product->get_id(), $selected_products)); ?>>
                            <?php echo $product->get_name(); ?>
                        </label>
                    </div>
                    <?php
                }
                ?>
            </div>
            <p class="submit">
                <input type="submit" name="pcm_edit_collection" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}

// Enqueue necessary scripts and styles
function pcm_enqueue_scripts($hook) {
    if ($hook != 'toplevel_page_product-collections' && $hook != 'product-collections_page_create-collection' && $hook != 'product-collections_page_edit-collection') {
        return;
    }
    wp_enqueue_style('pcm-styles', plugin_dir_url(__FILE__) . 'css/pcm-styles.css', array(), '1.4');
    wp_enqueue_script('pcm-script', plugin_dir_url(__FILE__) . 'js/pcm-script.js', array('jquery'), '1.4', true);
}
add_action('admin_enqueue_scripts', 'pcm_enqueue_scripts');

// Add success message
function pcm_admin_notices() {
    $screen = get_current_screen();
    if ($screen->id === 'toplevel_page_product-collections' && isset($_GET['message'])) {
        if ($_GET['message'] === 'created') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Collection created successfully!</p>
            </div>
            <?php
        } elseif ($_GET['message'] === 'updated') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Collection updated successfully!</p>
            </div>
            <?php
        }
    }
}
add_action('admin_notices', 'pcm_admin_notices');

// Register Elementor Widget
function pcm_register_elementor_widget() {
    // Check if Elementor is active
    if (did_action('elementor/loaded')) {
        require_once(__DIR__ . '/elementor-widget.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \PCM_Elementor_Widget());
    }
}
add_action('elementor/widgets/widgets_registered', 'pcm_register_elementor_widget');

// Enqueue styles for Elementor
function pcm_enqueue_elementor_styles() {
    wp_enqueue_style('pcm-styles', plugin_dir_url(__FILE__) . 'css/pcm-styles.css', array(), '1.4');
}
add_action('elementor/frontend/after_enqueue_styles', 'pcm_enqueue_elementor_styles');

// Add Elementor category
function pcm_add_elementor_widget_categories($elements_manager) {
    $elements_manager->add_category(
        'product-collections',
        [
            'title' => __('Product Collections', 'pcm'),
            'icon' => 'fa fa-plug',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'pcm_add_elementor_widget_categories');

// Initialize the plugin
function pcm_init() {
    // Load text domain for translations
    load_plugin_textdomain('pcm', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'pcm_init');