<?php
add_action('admin_menu', 'pcm_add_create_collection_page');

function pcm_add_create_collection_page() {
    add_submenu_page(
        null,
        'Create New Collection',
        'Create New Collection',
        'manage_options',
        'pcm-create-collection',
        'pcm_create_collection_page'
    );
}

function pcm_create_collection_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $collection_name = sanitize_text_field($_POST['collection_name']);
        $selected_products = isset($_POST['products']) ? array_map('intval', $_POST['products']) : array();

        $collections = get_option('pcm_collections', array());
        $collections[$collection_name] = $selected_products;
        update_option('pcm_collections', $collections);

        wp_redirect(admin_url('admin.php?page=pcm-dashboard'));
        exit;
    }

    // Get all products (assuming you're using WooCommerce)
    $products = wc_get_products(array('limit' => -1));
    ?>
    <div class="wrap">
        <h1>Create New Collection</h1>
        <form method="post">
            <label for="collection_name">Collection Name:</label>
            <input type="text" id="collection_name" name="collection_name" required>
            
            <h2>Products</h2>
            <?php foreach ($products as $product) : ?>
                <label>
                    <input type="checkbox" name="products[]" value="<?php echo $product->get_id(); ?>">
                    <?php echo $product->get_name(); ?>
                </label><br>
            <?php endforeach; ?>

            <input type="submit" class="button button-primary" value="Save Collection">
        </form>
    </div>
    <?php
}