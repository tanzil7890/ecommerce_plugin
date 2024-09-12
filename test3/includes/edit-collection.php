<?php
add_action('admin_menu', 'pcm_add_edit_collection_page');

function pcm_add_edit_collection_page() {
    add_submenu_page(
        null,
        'Edit Collection',
        'Edit Collection',
        'manage_options',
        'pcm-edit-collection',
        'pcm_edit_collection_page'
    );
}

function pcm_edit_collection_page() {
    $collection_name = isset($_GET['name']) ? sanitize_text_field($_GET['name']) : '';
    $collections = get_option('pcm_collections', array());

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_name = sanitize_text_field($_POST['collection_name']);
        $selected_products = isset($_POST['products']) ? array_map('intval', $_POST['products']) : array();

        unset($collections[$collection_name]);
        $collections[$new_name] = $selected_products;
        update_option('pcm_collections', $collections);

        wp_redirect(admin_url('admin.php?page=pcm-dashboard'));
        exit;
    }

    $collection_products = isset($collections[$collection_name]) ? $collections[$collection_name] : array();
    $products = wc_get_products(array('limit' => -1));
    ?>
    <div class="wrap">
        <h1>Edit Collection</h1>
        <form method="post">
            <label for="collection_name">Collection Name:</label>
            <input type="text" id="collection_name" name="collection_name" value="<?php echo esc_attr($collection_name); ?>" required>
            
            <h2>Products</h2>
            <?php foreach ($products as $product) : ?>
                <label>
                    <input type="checkbox" name="products[]" value="<?php echo $product->get_id(); ?>"
                        <?php checked(in_array($product->get_id(), $collection_products)); ?>>
                    <?php echo $product->get_name(); ?>
                </label><br>
            <?php endforeach; ?>

            <input type="submit" class="button button-primary" value="Update Collection">
        </form>
    </div>
    <?php
}