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
            foreach ($collections as $name => $products) {
                echo '<div class="pcm-collection-item" data-name="' . esc_attr(strtolower($name)) . '">';
                echo '<h3>' . esc_html($name) . '</h3>';
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
</style>