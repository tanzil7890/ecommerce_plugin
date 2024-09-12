<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$collection_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
var_dump($collection_id); // Debug line to check the ID
$collection = get_post($collection_id);

if (!$collection || $collection->post_type !== 'collection') {
    wp_die('Collection not found');
}

$selected_products = get_post_meta($collection_id, '_selected_products', true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_collection'])) {
    $collection_name = sanitize_text_field($_POST['collection_name']);
    $new_selected_products = isset($_POST['products']) ? array_map('intval', $_POST['products']) : array();

    // Update collection
    wp_update_post(array(
        'ID' => $collection_id,
        'post_title' => $collection_name,
    ));

    // Update selected products
    update_post_meta($collection_id, '_selected_products', $new_selected_products);

    // Redirect to dashboard with success message
    wp_redirect(admin_url('admin.php?page=my-collections&message=updated'));
    exit;
}
?>

<div class="wrap">
    <h1>Edit Collection</h1>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="collection_name">Collection Name</label></th>
                <td><input name="collection_name" type="text" id="collection_name" value="<?php echo esc_attr($collection->post_title); ?>" class="regular-text" required></td>
            </tr>
        </table>

        <h2>Products</h2>
        <p><label><input type="checkbox" id="select-all-products"> Select All</label></p>
        <div class="product-list">
            <?php
            $all_products = wc_get_products(array('status' => 'publish', 'limit' => -1));
            foreach ($all_products as $product) {
                $checked = in_array($product->get_id(), $selected_products) ? 'checked' : '';
                echo '<label><input type="checkbox" name="products[]" value="' . esc_attr($product->get_id()) . '" ' . $checked . '> ' . esc_html($product->get_name()) . '</label><br>';
            }
            ?>
        </div>

        <?php submit_button('Update Collection', 'primary', 'update_collection'); ?>
    </form>
</div>