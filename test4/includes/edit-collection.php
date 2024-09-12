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
    if (!isset($_GET['name'])) {
        wp_die('Collection name is required.');
    }

    $collection_name = sanitize_text_field($_GET['name']);
    $collections = get_option('pcm_collections', array());

    if (!isset($collections[$collection_name])) {
        wp_die('Collection not found.');
    }

    $collection = $collections[$collection_name];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $old_name = sanitize_text_field($_GET['name']); // Get the old name from the URL
        $new_name = sanitize_text_field($_POST['collection_name']);
        $collection_description = sanitize_textarea_field($_POST['collection_description']);
        $segmentation_type = sanitize_text_field($_POST['segmentation_type']);
        $recommendation_type = sanitize_text_field($_POST['recommendation_type']);
        $price_filter = sanitize_text_field($_POST['price_filter']);
        $selected_products = isset($_POST['products']) ? array_map('intval', $_POST['products']) : array();
    
        $collection_data = array(
            'name' => $new_name,
            'description' => $collection_description,
            'segmentation_type' => $segmentation_type,
            'recommendation_type' => $recommendation_type,
            'price_filter' => $price_filter,
            'products' => $selected_products,
        );
    
        $collections = get_option('pcm_collections', array());
        
        // Remove the old collection if the name has changed
        if ($old_name !== $new_name) {
            unset($collections[$old_name]);
        }
        
        $collections[$new_name] = $collection_data;
        update_option('pcm_collections', $collections);
    
        wp_redirect(admin_url('admin.php?page=pcm-dashboard'));
        exit;
    }

    // Get all products (assuming you're using WooCommerce)
    $products = wc_get_products(array('limit' => -1));
    ?>
    <div class="wrap">
        <h1>Edit Collection</h1>
        <form method="post" id="pcm-collection-form">
            <div class="pcm-row">
                <div class="pcm-column">
                    <label for="collection_name">Name:</label>
                    <input type="text" id="collection_name" name="collection_name" value="<?php echo esc_attr($collection['name']); ?>" required>
                </div>
                <div class="pcm-column">
                    <label for="collection_description">Description:</label>
                    <textarea id="collection_description" name="collection_description"><?php echo esc_textarea($collection['description']); ?></textarea>
                </div>
            </div>

            <div class="pcm-row">
                <div class="pcm-column">
                    <h3>Type of Drag/Drop [Segmentation]:</h3>
                    <select id="segmentation_type" name="segmentation_type">
                        <option value="categories" <?php selected($collection['segmentation_type'], 'categories'); ?>>Categories</option>
                        <option value="brand" <?php selected($collection['segmentation_type'], 'brand'); ?>>Brand</option>
                        <option value="gender" <?php selected($collection['segmentation_type'], 'gender'); ?>>Gender</option>
                        <option value="custom" <?php selected($collection['segmentation_type'], 'custom'); ?>>Custom</option>
                    </select>
                </div>
                <div class="pcm-column">
                    <h3>Type of Drag/Drop [Recommendation]:</h3>
                    <select id="recommendation_type" name="recommendation_type">
                        <option value="price" <?php selected($collection['recommendation_type'], 'price'); ?>>Price</option>
                        <option value="best_seller" <?php selected($collection['recommendation_type'], 'best_seller'); ?>>Best Seller</option>
                        <option value="new_arrival" <?php selected($collection['recommendation_type'], 'new_arrival'); ?>>New Arrival</option>
                        <option value="rating" <?php selected($collection['recommendation_type'], 'rating'); ?>>Rating</option>
                    </select>
                </div>
            </div>

            <div class="pcm-row">
                <div class="pcm-column">
                    <h3>Filters:</h3>
                    <select id="price_filter" name="price_filter">
                        <option value="" <?php selected($collection['price_filter'], ''); ?>>No filter</option>
                        <option value="high_low" <?php selected($collection['price_filter'], 'high_low'); ?>>Price: High to Low</option>
                        <option value="low_high" <?php selected($collection['price_filter'], 'low_high'); ?>>Price: Low to High</option>
                    </select>
                </div>
                <div class="pcm-column">
                    <h3>Product Search:</h3>
                    <input type="text" id="product_search" name="product_search" placeholder="Search products...">
                </div>
            </div>

            <div class="pcm-row">
                <div class="pcm-column full-width">
                    <h3>Product List:</h3>
                    <div id="product_list">
                        <?php foreach ($products as $product) : ?>
                            <label>
                                <input type="checkbox" name="products[]" value="<?php echo $product->get_id(); ?>"
                                    <?php checked(in_array($product->get_id(), $collection['products'])); ?>>
                                <?php echo $product->get_name(); ?> - $<?php echo $product->get_price(); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <input type="submit" class="button button-primary" value="Update Collection">
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function filterProducts() {
            var segmentationType = $('#segmentation_type').val();
            var recommendationType = $('#recommendation_type').val();
            var priceFilter = $('#price_filter').val();
            var searchTerm = $('#product_search').val().toLowerCase();

            $('#product_list input[type="checkbox"]').each(function() {
                var $label = $(this).parent();
                var productName = $label.text().toLowerCase();
                var productPrice = parseFloat($label.text().split('$')[1]);
                var show = true;

                // Filter by search term
                if (searchTerm && !productName.includes(searchTerm)) {
                    show = false;
                }

                // Filter by segmentation type
                // Note: This is a placeholder. You'll need to implement the actual logic based on your product data structure
                if (segmentationType === 'categories') {
                    // Implement category filtering logic
                } else if (segmentationType === 'brand') {
                    // Implement brand filtering logic
                } else if (segmentationType === 'gender') {
                    // Implement gender filtering logic
                } else if (segmentationType === 'custom') {
                    // Implement custom filtering logic
                }

                // Filter by recommendation type
                // Note: This is a placeholder. You'll need to implement the actual logic based on your product data structure
                if (recommendationType === 'price') {
                    // Implement price-based recommendation logic
                } else if (recommendationType === 'best_seller') {
                    // Implement best seller recommendation logic
                } else if (recommendationType === 'new_arrival') {
                    // Implement new arrival recommendation logic
                } else if (recommendationType === 'rating') {
                    // Implement rating-based recommendation logic
                }

                // Apply price filter
                if (priceFilter === 'high_low') {
                    // Sort products high to low
                    $label.data('price', productPrice);
                } else if (priceFilter === 'low_high') {
                    // Sort products low to high
                    $label.data('price', productPrice);
                }

                // Show/hide the product based on the filters
                $label.toggle(show);
            });

            // Apply sorting if a price filter is selected
            if (priceFilter) {
                var $productList = $('#product_list');
                var $products = $productList.children('label').get();
                $products.sort(function(a, b) {
                    var priceA = $(a).data('price');
                    var priceB = $(b).data('price');
                    return priceFilter === 'high_low' ? priceB - priceA : priceA - priceB;
                });
                $.each($products, function(idx, itm) { $productList.append(itm); });
            }
        }

        $('#segmentation_type, #recommendation_type, #price_filter').change(filterProducts);
        $('#product_search').on('input', filterProducts);
    });
    </script>
    <style>
        .pcm-row {
    display: flex;
    margin-bottom: 20px;
    }

    .pcm-column {
        flex: 1;
        padding: 0 10px;
    }

    .pcm-column.full-width {
        flex: 0 0 100%;
    }

    #product_list {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
    }

    /* Add more styles as needed */
    </style>
    <?php
}

