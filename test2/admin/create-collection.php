<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_collection'])) {
    // Process form submission
    $collection_name = sanitize_text_field($_POST['collection_name']);
    $selected_products = isset($_POST['products']) ? array_map('intval', $_POST['products']) : array();

    // Create new collection (custom post type)
    $collection_id = wp_insert_post(array(
        'post_title' => $collection_name,
        'post_type' => 'collection',
        'post_status' => 'publish',
    ));

    if ($collection_id) {
        // Save selected products as post meta
        update_post_meta($collection_id, '_selected_products', $selected_products);
        
        // Redirect to dashboard with success message
        wp_redirect(admin_url('admin.php?page=my-collections&message=created'));
        exit;
    }
}
?>

<div class="wrap">
    <h1>Create New Collection</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="collection_name">Collection Name</label></th>
                <td><input name="collection_name" type="text" id="collection_name" value="" class="regular-text" required></td>
            </tr>
        </table>

        <h2>Products</h2>
        <div class="product-list">
            <?php
            $products = wc_get_products(array('status' => 'publish', 'limit' => -1));
            foreach ($products as $product) {
                echo '<label><input type="checkbox" name="products[]" value="' . esc_attr($product->get_id()) . '"> ' . esc_html($product->get_name()) . '</label><br>';
            }
            ?>
        </div>

        <?php submit_button('Create Collection', 'primary', 'create_collection'); ?>
    </form>
</div>