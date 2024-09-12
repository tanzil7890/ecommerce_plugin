<div class="wrap">
    <h1>My Collections</h1>
    <div class="collection-actions">
        <a href="<?php echo admin_url('admin.php?page=create-new-collection'); ?>" class="button button-primary">Create New Collection</a>
        <input type="text" placeholder="Search collections..." id="search-collections">
    </div>
    <div class="collections-grid">
    <?php
    $collections = get_posts(array('post_type' => 'collection', 'posts_per_page' => -1));
    foreach ($collections as $collection) {
        $product_ids = get_post_meta($collection->ID, '_selected_products', true);
        $products = array_map('wc_get_product', $product_ids);
        ?>
        <div class="collection-item" id="collection-<?php echo $collection->ID; ?>">
            <h3><?php echo esc_html($collection->post_title); ?></h3>
            <p>Products: <?php echo count($products); ?></p>
            <ul class="product-list">
                <?php foreach ($products as $product) : ?>
                    <li><?php echo esc_html($product->get_name()); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="<?php echo admin_url('admin.php?page=edit-collection&id=' . $collection->ID); ?>" class="button edit-collection">Edit</a>
            <button class="button delete-collection" data-id="<?php echo $collection->ID; ?>">Delete</button>
        </div>
        <?php
    }
    ?>
    </div>
</div>