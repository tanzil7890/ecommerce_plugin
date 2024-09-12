jQuery(document).ready(function($) {
    const filterBox = $('#filter-box');
    const filterOptions = $('#filter-options');
    const productList = $('#product-list');
    const noProductsMessage = $('#no-products-message');

    let activeFilters = [];

    // Drag and drop functionality
    filterOptions.on('dragstart', '.filter-button', function(e) {
        e.originalEvent.dataTransfer.setData('text/plain', $(this).data('filter'));
    });

    filterBox.on('dragover', function(e) {
        e.preventDefault();
    });

    filterBox.on('drop', function(e) {
        e.preventDefault();
        const filterType = e.originalEvent.dataTransfer.getData('text');
        if (!activeFilters.includes(filterType)) {
            activeFilters.push(filterType);
            $(this).append(`<button type="button" class="active-filter" data-filter="${filterType}">${filterType}</button>`);
            updateProductList();
        }
    });

    // Remove filter when clicked
    filterBox.on('click', '.active-filter', function() {
        const filterType = $(this).data('filter');
        activeFilters = activeFilters.filter(f => f !== filterType);
        $(this).remove();
        updateProductList();
    });

    function updateProductList() {
        productList.empty();
        
        if (activeFilters.includes('categories')) {
            if (activeFilters.includes('gender')) {
                appendCategoriesWithGenderGroups();
            } else {
                appendCategoriesWithProducts();
            }
        } else if (activeFilters.includes('gender')) {
            appendGenderGroups();
        } else {
            appendAllProducts();
        }

        if (productList.children().length === 0) {
            noProductsMessage.show();
        } else {
            noProductsMessage.hide();
        }

        // Apply current sorting
        sortProducts($('#price-sort').val());
    }
    

    function appendCategoriesWithGenderGroups() {
        categories.forEach(category => {
            const categoryProducts = productData.filter(product => 
                product.categories.includes(parseInt(category.term_id))
            );
            
            if (categoryProducts.length > 0) {
                const genders = ['men', 'women', 'unisex'];
                let categoryHtml = `
                    <div class="category-group" data-category-id="${category.term_id}">
                        <label>
                            <input type="checkbox" class="category-checkbox" data-category-id="${category.term_id}">
                            ${category.name}
                        </label>
                        <div class="category-products">
                `;

                genders.forEach(gender => {
                    const genderProducts = categoryProducts.filter(product => product.gender === gender);
                    if (genderProducts.length > 0) {
                        categoryHtml += `
                            <div class="gender-group" data-gender="${gender}">
                                <label>
                                    <input type="checkbox" class="gender-checkbox" data-gender="${gender}">
                                    ${gender.charAt(0).toUpperCase() + gender.slice(1)}
                                </label>
                                <div class="gender-products">
                                    ${genderProducts.map(product => getProductHtml(product)).join('')}
                                </div>
                            </div>
                        `;
                    }
                });

                categoryHtml += `
                        </div>
                    </div>
                `;
                productList.append(categoryHtml);
            }
        });
    }


    function appendCategoriesWithProducts() {
        categories.forEach(category => {
            const categoryProducts = productData.filter(product => 
                product.categories.includes(parseInt(category.term_id))
            );
            
            if (categoryProducts.length > 0) {
                const categoryHtml = `
                    <div class="category-group" data-category-id="${category.term_id}">
                        <label>
                            <input type="checkbox" class="category-checkbox" data-category-id="${category.term_id}">
                            ${category.name}
                        </label>
                        <div class="category-products">
                            ${categoryProducts.map(product => getProductHtml(product)).join('')}
                        </div>
                    </div>
                `;
                productList.append(categoryHtml);
            }
        });
    }

    function appendGenderGroups() {
        const genders = ['men', 'women', 'unisex'];
        genders.forEach(gender => {
            const genderProducts = productData.filter(product => product.gender === gender);
            if (genderProducts.length > 0) {
                const genderHtml = `
                    <div class="gender-group" data-gender="${gender}">
                        <label>
                            <input type="checkbox" class="gender-checkbox" data-gender="${gender}">
                            ${gender.charAt(0).toUpperCase() + gender.slice(1)}
                        </label>
                        <div class="gender-products">
                            ${genderProducts.map(product => getProductHtml(product)).join('')}
                        </div>
                    </div>
                `;
                productList.append(genderHtml);
            }
        });
    }

    function appendAllProducts() {
        productData.forEach(product => {
            productList.append(getProductHtml(product));
        });
    }

    function getProductHtml(product) {
        return `
            <div class="product-item" data-price="${product.price}" data-name="${product.name.toLowerCase()}">
                <label>
                    <input type="checkbox" name="products[]" value="${product.id}">
                    <span class="product-name">${product.name}</span>
                    <span class="product-price">${formatPrice(product.price)}</span>
                </label>
            </div>
        `;
    }


    function formatPrice(price) {
        return '$' + parseFloat(price).toFixed(2);
    }

    // Hierarchical selection
    productList.on('change', '.category-checkbox', function() {
        const isChecked = $(this).prop('checked');
        $(this).closest('.category-group').find('input[type="checkbox"]').prop('checked', isChecked);
    });

    productList.on('change', '.gender-checkbox', function() {
        const isChecked = $(this).prop('checked');
        $(this).closest('.gender-group').find('.product-item input[type="checkbox"]').prop('checked', isChecked);
    });

    // Initialize product list
    updateProductList();

    // Existing search functionality
    $('#product-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.product-item').each(function() {
            const productName = $(this).data('name');
            $(this).toggle(productName.includes(searchTerm));
        });
        updateVisibility();
    });

    // Updated sort functionality
    $('#price-sort').on('change', function() {
        sortProducts($(this).val());
    });

    function sortProducts(sortOrder) {
        if (activeFilters.includes('categories')) {
            if (activeFilters.includes('gender')) {
                $('.category-group').each(function() {
                    $(this).find('.gender-group').each(function() {
                        sortProductsInContainer($(this).find('.gender-products'), sortOrder);
                    });
                });
            } else {
                $('.category-group').each(function() {
                    sortProductsInContainer($(this).find('.category-products'), sortOrder);
                });
            }
        } else if (activeFilters.includes('gender')) {
            $('.gender-group').each(function() {
                sortProductsInContainer($(this).find('.gender-products'), sortOrder);
            });
        } else {
            sortProductsInContainer(productList, sortOrder);
        }
    }

    function sortProductsInContainer($container, sortOrder) {
        const $products = $container.children('.product-item');
        $products.sort(function(a, b) {
            const priceA = parseFloat($(a).data('price'));
            const priceB = parseFloat($(b).data('price'));
            return sortOrder === 'high-low' ? priceB - priceA : priceA - priceB;
        });
        $container.append($products);
    }

    function updateVisibility() {
        $('.category-group').each(function() {
            const $category = $(this);
            const $visibleProducts = $category.find('.product-item:visible');
            if ($visibleProducts.length === 0) {
                $category.hide();
            } else {
                $category.show();
            }
        });

        $('.gender-group').each(function() {
            const $gender = $(this);
            const $visibleProducts = $gender.find('.product-item:visible');
            if ($visibleProducts.length === 0) {
                $gender.hide();
            } else {
                $gender.show();
            }
        });

        if ($('.product-item:visible').length === 0) {
            noProductsMessage.show();
        } else {
            noProductsMessage.hide();
        }
    }
});