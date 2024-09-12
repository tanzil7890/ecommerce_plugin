jQuery(document).ready(function($) {
    // Function to filter products based on search input
    function filterProducts() {
        var searchTerm = $('#product-search').val().toLowerCase();
        $('.product-item').each(function() {
            var productName = $(this).text().toLowerCase();
            if (productName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Add search input for products
    $('<input type="text" id="product-search" placeholder="Search products...">').insertBefore('h2:contains("Products")');

    // Wrap each product checkbox in a div for easier manipulation
    $('input[name="selected_products[]"]').each(function() {
        $(this).next('br').remove();
        $(this).wrap('<div class="product-item"></div>');
    });

    // Add event listener for product search
    $('#product-search').on('keyup', filterProducts);

    // Select/Deselect all products
    $('<button type="button" id="select-all">Select All</button>').insertAfter('h2:contains("Products")');
    $('<button type="button" id="deselect-all">Deselect All</button>').insertAfter('#select-all');

    $('#select-all').click(function() {
        $('.product-item:visible input[type="checkbox"]').prop('checked', true);
        updateSelectedCount();
    });

    $('#deselect-all').click(function() {
        $('.product-item:visible input[type="checkbox"]').prop('checked', false);
        updateSelectedCount();
    });

    // Show selected products count
    $('<div id="selected-count">Selected: 0</div>').insertAfter('#deselect-all');

    function updateSelectedCount() {
        var selectedCount = $('input[name="selected_products[]"]:checked').length;
        $('#selected-count').text('Selected: ' + selectedCount);
    }

    $('input[name="selected_products[]"]').change(updateSelectedCount);

    // Initial count update
    updateSelectedCount();

    // Confirm before submitting the form
    $('form').submit(function(e) {
        var collectionName = $('#collection_name').val();
        var selectedCount = $('input[name="selected_products[]"]:checked').length;
        
        if (!confirm('Create collection "' + collectionName + '" with ' + selectedCount + ' products?')) {
            e.preventDefault();
        }
    });

    // Elementor widget preview update
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        elementorFrontend.hooks.addAction('frontend/element_ready/pcm_elementor_widget.default', function($scope) {
            console.log('PCM Elementor Widget is ready!');

            var $productCards = $scope.find('.pcm-product-card');
            var $productGrid = $scope.find('.pcm-product-grid');

            // Function to sort product cards by rating
            function sortProductsByRating() {
                var $cards = $productGrid.children('.pcm-product-card').get();
                $cards.sort(function(a, b) {
                    var ratingA = parseFloat($(a).find('.star-rating').attr('title')) || 0;
                    var ratingB = parseFloat($(b).find('.star-rating').attr('title')) || 0;
                    return ratingB - ratingA;
                });
                $.each($cards, function(idx, item) { $productGrid.append(item); });
            }

            // Check if "Show Top Rated Products First" is enabled
            var showTopRatedFirst = $productGrid.data('show-top-rated-first');
            if (showTopRatedFirst === 'yes') {
                sortProductsByRating();
            }

            // Add hover effect to product cards
            $productCards.hover(
                function() {
                    $(this).css('transform', 'scale(1.05)');
                },
                function() {
                    $(this).css('transform', 'scale(1)');
                }
            );

            // Add click event to product cards
            $productCards.click(function() {
                alert('You clicked on: ' + $(this).find('.pcm-product-title').text());
            });

            // Lazy load images
            $scope.find('.pcm-product-image img').each(function() {
                var $img = $(this);
                $img.attr('src', $img.data('src'));
            });

            // Add a "Quick View" button to each product card
            $productCards.each(function() {
                var $card = $(this);
                var $quickViewBtn = $('<button>', {
                    text: 'Quick View',
                    class: 'pcm-quick-view-btn',
                    click: function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var productTitle = $card.find('.pcm-product-title').text();
                        var productPrice = $card.find('.pcm-product-price').text();
                        alert('Quick View for ' + productTitle + '\nPrice: ' + productPrice);
                    }
                });
                $card.append($quickViewBtn);
            });

            // Initialize a simple carousel if there are more than 4 products
            if ($productCards.length > 4) {
                var currentIndex = 0;
                var $nextBtn = $('<button>', { text: 'Next', class: 'pcm-carousel-btn pcm-next-btn' });
                var $prevBtn = $('<button>', { text: 'Prev', class: 'pcm-carousel-btn pcm-prev-btn' });

                $scope.append($prevBtn).append($nextBtn);

                function updateCarousel() {
                    $productCards.hide().slice(currentIndex, currentIndex + 4).show();
                    $prevBtn.prop('disabled', currentIndex === 0);
                    $nextBtn.prop('disabled', currentIndex + 4 >= $productCards.length);
                }

                $nextBtn.click(function() {
                    if (currentIndex + 4 < $productCards.length) {
                        currentIndex += 4;
                        updateCarousel();
                    }
                });

                $prevBtn.click(function() {
                    if (currentIndex > 0) {
                        currentIndex -= 4;
                        updateCarousel();
                    }
                });

                updateCarousel();
            }
        });
    }
});