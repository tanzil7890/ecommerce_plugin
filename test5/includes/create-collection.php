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
                <?php foreach ($products as $product) : ?>
                    <div class="product-item" data-price="<?php echo esc_attr($product->get_price()); ?>" data-name="<?php echo esc_attr(strtolower($product->get_name())); ?>">
                        <label>
                            <input type="checkbox" name="products[]" value="<?php echo $product->get_id(); ?>">
                            <span class="product-name"><?php echo $product->get_name(); ?></span>
                            <span class="product-price"><?php echo wc_price($product->get_price()); ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <p id="no-products-message" style="display: none;">No products found matching your search.</p>

            <input type="submit" class="button button-primary" value="Save Collection">
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function sortProducts(sortOrder) {
            var $productList = $('#product-list');
            var $products = $productList.children('.product-item:visible');

            if (sortOrder === 'high-low') {
                $products.sort(function(a, b) {
                    return $(b).data('price') - $(a).data('price');
                });
            } else if (sortOrder === 'low-high') {
                $products.sort(function(a, b) {
                    return $(a).data('price') - $(b).data('price');
                });
            }

            $productList.append($products);
        }

        $('#price-sort').on('change', function() {
            sortProducts($(this).val());
        });

        $('#product-search').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            var $products = $('.product-item');
            var visibleProducts = 0;

            $products.each(function() {
                var productName = $(this).data('name');
                if (productName.includes(searchTerm)) {
                    $(this).show();
                    visibleProducts++;
                } else {
                    $(this).hide();
                }
            });

            if (visibleProducts === 0) {
                $('#no-products-message').show();
            } else {
                $('#no-products-message').hide();
            }

            sortProducts($('#price-sort').val());
        });
    });
    </script>
    <?php
}