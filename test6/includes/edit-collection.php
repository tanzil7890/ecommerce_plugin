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
        $new_description = sanitize_textarea_field($_POST['collection_description']);
        $selected_products = isset($_POST['products']) ? array_map('intval', $_POST['products']) : array();

        unset($collections[$collection_name]);
        $collections[$new_name] = array(
            'description' => $new_description,
            'products' => $selected_products
        );
        update_option('pcm_collections', $collections);

        wp_redirect(admin_url('admin.php?page=pcm-dashboard'));
        exit;
    }

    $collection_data = isset($collections[$collection_name]) ? $collections[$collection_name] : array('description' => '', 'products' => array());
    $products = wc_get_products(array('limit' => -1));
    ?>
    <div class="wrap">
        <h1>Edit Collection</h1>
        <form method="post">
            <div class="form-field form-required term-name-wrap">
                <label for="collection_name">Collection Name:</label>
                <input type="text" id="collection_name" name="collection_name" value="<?php echo esc_attr($collection_name); ?>" required>
            </div>
            <div class="form-field term-description-wrap">
                <label for="collection_description">Description:</label>
                <textarea id="collection_description" name="collection_description"><?php echo esc_textarea($collection_data['description']); ?></textarea>
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
                            <input type="checkbox" name="products[]" value="<?php echo $product->get_id(); ?>"
                                <?php checked(in_array($product->get_id(), $collection_data['products'])); ?>>
                            <span class="product-name"><?php echo $product->get_name(); ?></span>
                            <span class="product-price"><?php echo wc_price($product->get_price()); ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <p id="no-products-message" style="display: none;">No products found matching your search.</p>

            <input type="submit" class="button button-primary" value="Update Collection">
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

