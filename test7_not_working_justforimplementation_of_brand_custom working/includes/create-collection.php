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
        $collection_description = sanitize_textarea_field($_POST['collection_description']);
        $selected_products = isset($_POST['products']) ? array_map('intval', $_POST['products']) : array();

        $collections = get_option('pcm_collections', array());
        $collections[$collection_name] = array(
            'description' => $collection_description,
            'products' => $selected_products
        );
        update_option('pcm_collections', $collections);

        wp_redirect(admin_url('admin.php?page=pcm-dashboard'));
        exit;
    }

    // Get all products (assuming you're using WooCommerce)
    $products = wc_get_products(array('limit' => -1));

    // Get all product categories
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
    ));

    // Prepare product data
    $product_data = array();
    foreach ($products as $product) {
        $product_cats = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'ids'));
        $product_tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
        
        $gender = 'unisex';
        if (in_array('men', array_map('strtolower', $product_tags))) {
            $gender = 'men';
        } elseif (in_array('women', array_map('strtolower', $product_tags))) {
            $gender = 'women';
        }

        $product_data[] = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'categories' => $product_cats,
            'gender' => $gender
        );
    }

    // Prepare category data for JavaScript
    $category_data = array_map(function($cat) {
        return array(
            'term_id' => $cat->term_id,
            'name' => $cat->name,
        );
    }, $categories);

    ?>
    <div class="wrap">
        <h1>Create New Collection</h1>
        <form method="post">
            <div class="form-field form-required term-name-wrap">
                <label for="collection_name">Collection Name:</label>
                <input type="text" id="collection_name" name="collection_name" required>
            </div>
            <div class="form-field term-description-wrap">
                <label for="collection_description">Description:</label>
                <textarea id="collection_description" name="collection_description"></textarea>
            </div>
            
            <h2>Filter Options</h2>
            <div id="filter-box" class="filter-box">
                <p>Drop filters here</p>
            </div>
            <div id="filter-options" class="filter-options">
                <button type="button" class="filter-button" draggable="true" data-filter="categories">Categories</button>
                <button type="button" class="filter-button" draggable="true" data-filter="gender">Gender</button>
                <button type="button" class="filter-button" draggable="true" data-filter="brand">Brand</button>
                <button type="button" class="filter-button" draggable="true" data-filter="custom">Custom</button>
            </div>

            <h2>Products</h2>
            <div class="product-filter">
                <label for="product-search">Search Products:</label>
                <input type="text" id="product-search" placeholder="Enter product name...">
                <label for="price-sort">Sort by Price:</label>
                <select id="price-sort">
                    <option value="">Select</option>
                    <option value="high-low">High to Low</option>
                    <option value="low-high">Low to High</option>
                </select>
            </div>
            <div id="product-list" class="product-grid">
                <!-- Product list will be populated by JavaScript -->
            </div>
            <p id="no-products-message" style="display: none;">No products found matching your criteria.</p>

            <input type="submit" class="button button-primary" value="Save Collection">
        </form>
    </div>

    <script>
    var productData = <?php echo json_encode($product_data); ?>;
    var categories = <?php echo json_encode($category_data); ?>;
    </script>

    <?php
}