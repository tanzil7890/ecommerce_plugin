jQuery(document).ready(function($) {
    // Search functionality for collections
    $('#search-collections').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.collection-item').each(function() {
            var collectionName = $(this).text().toLowerCase();
            if (collectionName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Toggle select all products
    $('#select-all-products').on('change', function() {
        $('.product-checkbox').prop('checked', this.checked);
    });

    // Update select all checkbox state
    $('.product-checkbox').on('change', function() {
        var allChecked = $('.product-checkbox:checked').length === $('.product-checkbox').length;
        $('#select-all-products').prop('checked', allChecked);
    });

    // Pagination for products (if there are many)
    var productsPerPage = 20;
    var currentPage = 1;

    function showProducts() {
        var start = (currentPage - 1) * productsPerPage;
        var end = start + productsPerPage;

        $('.product-item').hide().slice(start, end).show();
        updatePaginationButtons();
    }

    function updatePaginationButtons() {
        var totalPages = Math.ceil($('.product-item').length / productsPerPage);
        $('#prev-page').prop('disabled', currentPage === 1);
        $('#next-page').prop('disabled', currentPage === totalPages);
        $('#current-page').text(currentPage + ' / ' + totalPages);
    }

    $('#prev-page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            showProducts();
        }
    });

    $('#next-page').on('click', function() {
        var totalPages = Math.ceil($('.product-item').length / productsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            showProducts();
        }
    });

    // Initialize product display
    showProducts();

    // AJAX for deleting a collection
    $('.delete-collection').on('click', function(e) {
        e.preventDefault();
        var collectionId = $(this).data('id');

        if (confirm('Are you sure you want to delete this collection?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_collection',
                    nonce: myCollectionPlugin.nonce,
                    collection_id: collectionId
                },
                success: function(response) {
                    if (response.success) {
                        $('#collection-' + collectionId).remove();
                        alert('Collection deleted successfully!');
                    } else {
                        alert('Error deleting collection: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });

    // Handle editing a collection
    $('.edit-collection').on('click', function(e) {
        e.preventDefault();
        var collectionId = $(this).data('id');
        
        // Redirect to edit page
        window.location.href = ajaxurl + '?action=edit_collection&id=' + collectionId;
    });

    $('#select-all-products').on('change', function() {
        $('input[name="products[]"]').prop('checked', this.checked);
    });

    // Update "Select All" checkbox based on individual product selections
    // Update "Select All" checkbox based on individual product selections
    $('input[name="products[]"]').on('change', function() {
        var allChecked = $('input[name="products[]"]:checked').length === $('input[name="products[]"]').length;
        $('#select-all-products').prop('checked', allChecked);
    });

    // Initialize "Select All" checkbox state
    var allChecked = $('input[name="products[]"]:checked').length === $('input[name="products[]"]').length;
    $('#select-all-products').prop('checked', allChecked);
});
