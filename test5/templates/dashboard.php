<div class="wrap">
    <h1>Product Collection Manager</h1>
    <div class="pcm-dashboard-header">
        <a href="<?php echo admin_url('admin.php?page=pcm-create-collection'); ?>" class="button button-primary">Create New Collection</a>
        <input type="text" id="pcm-search" placeholder="Search collections...">
    </div>
    <div id="pcm-collections-grid" class="pcm-collections-grid">
        <?php
        $collections = get_option('pcm_collections', array());
        if (empty($collections)) {
            echo '<p id="no-collections-message">No collections found. Create your first collection!</p>';
        } else {
            foreach ($collections as $name => $data) {
                $description = isset($data['description']) ? $data['description'] : '';
                $products = isset($data['products']) ? $data['products'] : array();
                
                echo '<div class="pcm-collection-item" data-name="' . esc_attr(strtolower($name)) . '">';
                echo '<h3>' . esc_html($name) . '</h3>';
                echo '<p class="description">' . esc_html($description) . '</p>';
                echo '<p>' . count($products) . ' products</p>';
                echo '<a href="' . admin_url('admin.php?page=pcm-edit-collection&name=' . urlencode($name)) . '" class="button">Edit</a>';
                echo '<button class="button delete-collection" data-name="' . esc_attr($name) . '">Delete</button>';
                echo '</div>';
            }
        }
        ?>
    </div>
    <p id="no-results-message" style="display: none;">No collections found matching your search.</p>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('pcm-search');
    const collectionsGrid = document.getElementById('pcm-collections-grid');
    const collectionItems = collectionsGrid.getElementsByClassName('pcm-collection-item');
    const noResultsMessage = document.getElementById('no-results-message');
    const noCollectionsMessage = document.getElementById('no-collections-message');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let visibleCount = 0;

        Array.from(collectionItems).forEach(function(item) {
            const collectionName = item.getAttribute('data-name');
            if (collectionName.includes(searchTerm)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0 && collectionItems.length > 0) {
            noResultsMessage.style.display = 'block';
            noCollectionsMessage.style.display = 'none';
        } else {
            noResultsMessage.style.display = 'none';
            noCollectionsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    });

    // Add delete functionality
    const deleteButtons = document.getElementsByClassName('delete-collection');
    Array.from(deleteButtons).forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this collection?')) {
                const collectionName = this.getAttribute('data-name');
                const xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxurl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    } else {
                        alert('Error deleting collection. Please try again.');
                    }
                };
                xhr.send('action=pcm_delete_collection&name=' + encodeURIComponent(collectionName) + '&nonce=' + '<?php echo wp_create_nonce('pcm_delete_collection'); ?>');
            }
        });
    });

});


</script>

<style>
.pcm-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.pcm-collections-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.pcm-collection-item {
    border: 1px solid #ddd;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 5px;
    text-align: center;
}

.pcm-collection-item h3 {
    margin-top: 0;
}

.pcm-collection-item .button {
    display: inline-block;
    margin-top: 10px;
}

.pcm-collection-item .delete-collection {
    margin-left: 10px;
    color: #a00;
}

.pcm-collection-item .delete-collection:hover {
    color: #dc3232;
}


#pcm-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

#pcm-modal > div {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
}

#pcm-modal h2 {
    margin-top: 0;
}

#pcm-modal button {
    margin-right: 10px;
}

.pcm-collection-item .description {
    margin: 5px 0;
    font-style: italic;
}

/* Existing styles... */

.product-filter {
    margin-bottom: 20px;
}

.product-filter label {
    margin-right: 10px;
}

#product-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
}

.product-item {
    margin-bottom: 5px;
}

.product-item label {
    display: block;
}

/* Existing styles... */

.product-filter {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.product-filter label {
    margin-right: 5px;
}

#product-search {
    width: 200px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 15px;
}

.product-item {
    background-color: #f9f9f9;
    border: 1px solid #e0e0e0;
    padding: 10px;
    border-radius: 4px;
}

.product-item label {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.product-name {
    font-weight: bold;
}

.product-price {
    color: #007cba;
}

#no-products-message {
    margin-top: 15px;
    font-style: italic;
    color: #666;
}

</style>