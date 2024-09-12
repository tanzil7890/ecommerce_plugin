jQuery(document).ready(function($) {
    // Function to filter and sort products
    function filterAndSortProducts() {
        var segmentationType = $('#segmentation_type').val();
        var recommendationType = $('#recommendation_type').val();
        var priceFilter = $('#price_filter').val();
        var searchTerm = $('#product_search').val().toLowerCase();

        $('#product_list label').each(function() {
            var $label = $(this);
            var productName = $label.text().toLowerCase();
            var productPrice = parseFloat($label.text().split('$')[1]);
            var show = true;

            // Filter by search term
            if (searchTerm && !productName.includes(searchTerm)) {
                show = false;
            }

            // Filter by segmentation type
            switch (segmentationType) {
                case 'categories':
                    var category = $label.data('category');
                    show = (category === $('#category-filter').val());
                    break;
                case 'brand':
                    var brand = $label.data('brand');
                    show = (brand === $('#brand-filter').val());
                    break;
                case 'gender':
                    var gender = $label.data('gender');
                    show = (gender === $('#gender-filter').val());
                    break;
                case 'custom':
                    var customValue = $label.data('custom');
                    show = (customValue === $('#custom-filter').val());
                    break;
            }

            // Filter by recommendation type
            switch (recommendationType) {
                case 'price':
                    var minPrice = parseFloat($('#min-price').val());
                    var maxPrice = parseFloat($('#max-price').val());
                    show = (productPrice >= minPrice && productPrice <= maxPrice);
                    break;
                case 'best_seller':
                    var isBestSeller = $label.data('bestseller');
                    show = isBestSeller;
                    break;
                case 'new_arrival':
                    var isNewArrival = $label.data('newarrival');
                    show = isNewArrival;
                    break;
                case 'rating':
                    var rating = parseFloat($label.data('rating'));
                    var minRating = parseFloat($('#min-rating').val());
                    show = (rating >= minRating);
                    break;
            }

            // Store the price for sorting
            $label.data('price', productPrice);

            // Show/hide the product based on the filters
            $label.toggle(show);
        });

        // Sort products if a price filter is selected
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

    // Event listeners for filter changes
    $('#segmentation_type, #recommendation_type, #price_filter').change(filterAndSortProducts);
    $('#product_search').on('input', filterAndSortProducts);

    // Drag and drop functionality
    function initDragAndDrop() {
        $('#product_list').sortable({
            items: 'label',
            cursor: 'move',
            placeholder: 'ui-state-highlight',
            update: function(event, ui) {
                var newOrder = $(this).sortable('toArray', {attribute: 'data-product-id'});
                updateProductOrder(newOrder);
            }
        });
    }
    
    function updateProductOrder(newOrder) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'update_product_order',
                order: newOrder,
                collection_id: $('#collection_id').val()
            },
            success: function(response) {
                if (response.success) {
                    console.log('Product order updated successfully');
                } else {
                    console.error('Failed to update product order');
                }
            }
        });
    }

    // Initialize drag and drop
    initDragAndDrop();

    // Form validation
    $('#pcm-collection-form').submit(function(e) {
        var collectionName = $('#collection_name').val().trim();
        if (collectionName === '') {
            e.preventDefault();
            alert('Please enter a collection name.');
            return false;
        }

        var selectedProducts = $('input[name="products[]"]:checked').length;
        if (selectedProducts === 0) {
            e.preventDefault();
            alert('Please select at least one product for the collection.');
            return false;
        }

        return true;
    });

    // Custom segmentation input
    $('#segmentation_type').change(function() {
        if ($(this).val() === 'custom') {
            var customSegmentation = prompt('Enter custom segmentation criteria:');
            if (customSegmentation) {
                // Create a new select element for custom segmentation
                var $customSelect = $('<select>', {
                    id: 'custom-filter',
                    name: 'custom_filter'
                });
    
                // Split the custom segmentation string into options
                var options = customSegmentation.split(',');
                $.each(options, function(i, option) {
                    $customSelect.append($('<option>', {
                        value: option.trim(),
                        text: option.trim()
                    }));
                });
    
                // Replace any existing custom filter with the new one
                $('#custom-filter').remove();
                $('#segmentation_type').after($customSelect);
    
                // Add the custom segmentation to the products for filtering
                $('#product_list label').each(function() {
                    var $label = $(this);
                    var productName = $label.text().toLowerCase();
                    var customValue = options.find(option => productName.includes(option.trim().toLowerCase()));
                    $label.data('custom', customValue || '');
                });
    
                // Trigger filtering
                filterAndSortProducts();
            }
        } else {
            $('#custom-filter').remove();
        }
    });

    // Product selection counter
    function updateProductCount() {
        var count = $('input[name="products[]"]:checked').length;
        $('#selected-product-count').text(count + ' product(s) selected');
    }

    $('input[name="products[]"]').change(updateProductCount);
    updateProductCount(); // Initial count

    // Save as draft functionality
    $('#save-draft').click(function(e) {
        e.preventDefault();
        var formData = $('#pcm-collection-form').serialize();
        formData += '&action=save_collection_draft';
    
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Collection saved as draft successfully.');
                    // Optionally, update the UI to reflect the draft status
                    $('#draft-status').text('Draft saved at ' + new Date().toLocaleString());
                } else {
                    alert('Failed to save draft. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred while saving the draft.');
            }
        });
    });

    // Preview functionality
    $('#preview-collection').click(function(e) {
        e.preventDefault();
        var formData = $('#pcm-collection-form').serialize();
        formData += '&action=preview_collection';
    
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Open a new window or modal with the preview content
                    var previewWindow = window.open('', 'Collection Preview', 'width=800,height=600');
                    previewWindow.document.write(response.preview_html);
                    previewWindow.document.close();
                } else {
                    alert('Failed to generate preview. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred while generating the preview.');
            }
        });
    });

    // Bulk selection of products
    $('#select-all-products').click(function() {
        $('input[name="products[]"]').prop('checked', this.checked);
        updateProductCount();
    });

    // Enable/disable filters based on segmentation type
    $('#segmentation_type').change(function() {
        var segmentationType = $(this).val();
        $('#recommendation_type, #price_filter').prop('disabled', segmentationType === 'custom');
    });

    // Auto-save functionality
    var autoSaveTimer;
    var lastAutoSave = Date.now();

    $('#pcm-collection-form :input').on('change input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Check if at least 5 minutes have passed since the last auto-save
            if (Date.now() - lastAutoSave >= 5 * 60 * 1000) {
                autoSaveCollection();
            }
        }, 5000); // Wait for 5 seconds of inactivity before auto-saving
    });

    function autoSaveCollection() {
        var formData = $('#pcm-collection-form').serialize();
        formData += '&action=auto_save_collection';

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    console.log('Collection auto-saved successfully.');
                    lastAutoSave = Date.now();
                    // Optionally, update the UI to show the last auto-save time
                    $('#auto-save-status').text('Auto-saved at ' + new Date().toLocaleString());
                } else {
                    console.error('Failed to auto-save collection.');
                }
            },
            error: function() {
                console.error('An error occurred during auto-save.');
            }
        });
    }
});