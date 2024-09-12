jQuery(document).ready(function($) {
    const filterBox = $('#filter-box');
    const filterOptions = $('#filter-options');
    const productList = $('#product-list');
    const noProductsMessage = $('#no-products-message');
    const appliedFiltersInput = $('#applied_filters');
    const selectedProductList = $('#selected-product-list');

    let activeFilters = savedFilters || [];
    let selectedProductIds = new Set(savedProducts || []);

    // Initialize active filters
    activeFilters.forEach(filter => {
        filterBox.append(`<button type="button" class="active-filter" data-filter="${filter}">${filter}</button>`);
    });

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
        if (filterType !== 'brand' && filterType !== 'custom' && !activeFilters.includes(filterType)) {
            activeFilters.push(filterType);
            $(this).append(`<button type="button" class="active-filter" data-filter="${filterType}">${filterType}</button>`);
            updateProductList();
            updateAppliedFiltersInput();
        }
    });

    // Remove filter when clicked
    filterBox.on('click', '.active-filter', function() {
        const filterType = $(this).data('filter');
        activeFilters = activeFilters.filter(f => f !== filterType);
        $(this).remove();
        updateProductList();
        updateAppliedFiltersInput();
    });

    function updateAppliedFiltersInput() {
        appliedFiltersInput.val(JSON.stringify(activeFilters));
    }

    function updateProductList() {
        productList.empty();
        
        if (activeFilters.length > 0) {
            if (activeFilters.includes('gender') && activeFilters.includes('categories')) {
                // Determine the order of filtering
                const genderFirst = activeFilters.indexOf('gender') < activeFilters.indexOf('categories');

                if (genderFirst) {
                    appendGenderWithCategoriesGroups();
                } else {
                    appendCategoriesWithGenderGroups();
                }
            } else if (activeFilters.includes('gender')) {
                appendGenderGroups();
            } else if (activeFilters.includes('categories')) {
                appendCategoriesWithProducts();
            }

            // Add brand filter functionality
            const brandFilters = activeFilters.filter(filter => filter.startsWith('Brand:'));
            if (brandFilters.length > 0) {
                appendBrandGroups(brandFilters);
                return; // Exit the function after applying brand filters
            }

            // Add custom filter functionality
            const customFilters = activeFilters.filter(filter => filter.startsWith('Custom:'));
            if (customFilters.length > 0) {
                appendCustomGroups(customFilters);
            }
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

        // Update checkbox states based on selectedProductIds
        updateCheckboxStates();

        updateSelectedProductsCount();
        updateSelectedProductList();
    }

    // New appendGenderWithCategoriesGroups function
    function appendGenderWithCategoriesGroups() {
        const genders = ['men', 'women', 'unisex'];
        
        genders.forEach(gender => {
            const genderProducts = productData.filter(product => product.gender === gender);
            
            if (genderProducts.length > 0) {
                let genderHtml = `
                    <div class="gender-group" data-gender="${gender}">
                        <label>
                            <input type="checkbox" class="gender-checkbox" data-category-id="${gender}">
                            ${gender.charAt(0).toUpperCase() + gender.slice(1)}
                        </label>
                        <div class="gender-products">
                `;
    
                categories.forEach(category => {
                    const categoryProducts = genderProducts.filter(product => 
                        product.categories.includes(parseInt(category.term_id))
                    );
                    
                    if (categoryProducts.length > 0) {
                        genderHtml += `
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
                    }
                });
    
                genderHtml += `
                        </div>
                    </div>
                `;
                productList.append(genderHtml);
            }
        });
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
        const isChecked = selectedProductIds.has(product.id) ? 'checked' : '';
        return `
            <div class="product-item" data-price="${product.price}" data-name="${product.name.toLowerCase()}" data-id="${product.id}">
                <label>
                    <input type="checkbox" name="products[]" value="${product.id}" class="product-checkbox" ${isChecked}>
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
    productList.on('change', '.category-checkbox, .gender-checkbox', function() {
        const isChecked = $(this).prop('checked');
        const $products = $(this).closest('.category-group, .gender-group').find('.product-checkbox');
        $products.each(function() {
            updateProductSelection($(this), isChecked);
        });
        updateSelectedProductsCount();
    });

    // Product checkbox change
    productList.on('change', '.product-checkbox', function() {
        updateProductSelection($(this), $(this).prop('checked'));
        updateSelectedProductsCount();
    });

    function updateProductSelection($checkbox, isChecked) {
        const productId = parseInt($checkbox.val());
        if (isChecked) {
            selectedProductIds.add(productId);
        } else {
            selectedProductIds.delete(productId);
        }
        updateCheckboxStates();
    }

    function updateCheckboxStates() {
        $('#product-list input[type="checkbox"], #selected-product-list input[type="checkbox"]').each(function() {
            const productId = $(this).val();
            $(this).prop('checked', selectedProductIds.has(productId));
        });
    }

    function updateSelectedProductsCount() {
        const count = selectedProductIds.size;
        $('.selected-products-count').text(count + (count === 1 ? ' product' : ' products') + ' selected');
    }

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
            $category.toggle($visibleProducts.length > 0);
        });

        $('.gender-group').each(function() {
            const $gender = $(this);
            const $visibleProducts = $gender.find('.product-item:visible');
            $gender.toggle($visibleProducts.length > 0);
        });

        if ($('.product-item:visible').length === 0) {
            noProductsMessage.show();
        } else {
            noProductsMessage.hide();
        }
    }

    // Call updateAppliedFiltersInput on page load
    updateAppliedFiltersInput();

    // Add brand and custom filter buttons to filter options
    filterOptions.append('<button type="button" class="filter-button" draggable="true" data-filter="brand">Brand</button>');
    filterOptions.append('<button type="button" class="filter-button" draggable="true" data-filter="custom">Custom</button>');

    // Handle brand filter drop
    filterBox.on('drop', function(e) {
        const filterType = e.originalEvent.dataTransfer.getData('text');
        if (filterType === 'brand') {
            e.preventDefault();
            const brandName = prompt("Enter the brand name you want to filter:");
            if (brandName) {
                const brandFilter = `Brand: ${brandName}`;
                if (!activeFilters.includes(brandFilter)) {
                    activeFilters.push(brandFilter);
                    $(this).append(`<button type="button" class="active-filter" data-filter="${brandFilter}">${brandFilter}</button>`);
                    updateProductList();
                    updateAppliedFiltersInput();
                }
            }
        }
    });

    // Handle custom filter drop
    filterBox.on('drop', function(e) {
        const filterType = e.originalEvent.dataTransfer.getData('text');
        if (filterType === 'custom') {
            e.preventDefault();
            const customName = prompt("Enter a name for this custom recommendation collection:");
            if (customName) {
                const customFilter = `Custom: ${customName}`;
                if (!activeFilters.includes(customFilter)) {
                    activeFilters.push(customFilter);
                    $(this).append(`<button type="button" class="active-filter" data-filter="${customFilter}">${customFilter}</button>`);
                    updateProductList();
                    updateAppliedFiltersInput();
                }
            }
        }
    });

    // Add the appendBrandGroups function
    function appendBrandGroups(brandFilters) {
        productList.empty(); // Clear existing products

        brandFilters.forEach(brandFilter => {
            const brandName = brandFilter.split(': ')[1].toLowerCase();
            const brandProducts = productData.filter(product => 
                product.categories.some(cat => categories.find(c => c.term_id === cat).name.toLowerCase().includes(brandName)) ||
                (product.tags && product.tags.some(tag => tag.toLowerCase().includes(brandName)))
            );

            if (brandProducts.length > 0) {
                let brandHtml = `
                    <div class="brand-group" data-brand="${brandName}">
                        <h3>Brand: ${brandName}</h3>
                        <div class="brand-products">
                            ${brandProducts.map(product => getProductHtml(product)).join('')}
                        </div>
                    </div>
                `;
                productList.append(brandHtml);
            }
        });

        // Add "Other Brands" section
        const otherBrandProducts = productData.filter(product => 
            !brandFilters.some(brandFilter => {
                const brandName = brandFilter.split(': ')[1].toLowerCase();
                return product.categories.some(cat => categories.find(c => c.term_id === cat).name.toLowerCase().includes(brandName)) ||
                       (product.tags && product.tags.some(tag => tag.toLowerCase().includes(brandName)));
            })
        );

        if (otherBrandProducts.length > 0) {
            let otherBrandsHtml = `
                <div class="brand-group" data-brand="other">
                    <h3>Other Brands</h3>
                    <div class="brand-products">
                        ${otherBrandProducts.map(product => getProductHtml(product)).join('')}
                    </div>
                </div>
            `;
            productList.append(otherBrandsHtml);
        }

        if (productList.children().length === 0) {
            noProductsMessage.show();
        } else {
            noProductsMessage.hide();
        }

        // Apply current sorting
        sortProducts($('#price-sort').val());

        // Update checkbox states based on selectedProductIds
        updateCheckboxStates();
    }

    function appendCustomGroups(customFilters) {
        customFilters.forEach(customFilter => {
            const customName = customFilter.split(': ')[1];
            let customHtml = `
                <div class="custom-group" data-custom="${customName}">
                    <h3>Custom: ${customName}</h3>
                    <div class="custom-products">
                        ${productData.map(product => getProductHtml(product)).join('')}
                    </div>
                </div>
            `;
            productList.append(customHtml);
        });
    }

    function updateSelectedProductList() {
        selectedProductList.empty();
        productData.forEach(product => {
            if (selectedProductIds.has(product.id.toString())) {
                selectedProductList.append(getProductHtml(product, true));
            }
        });
        updateSelectedProductsCount();
    }

    function getProductHtml(product, isSelected = false) {
        return `
            <div class="product-item">
                <label>
                    <input type="checkbox" name="products[]" value="${product.id}" ${isSelected ? 'checked' : ''}>
                    <span class="product-name">${product.name}</span>
                    <span class="product-price">$${parseFloat(product.price).toFixed(2)}</span>
                </label>
            </div>
        `;
    }

    $('#product-list, #selected-product-list').on('change', 'input[type="checkbox"]', function() {
        const productId = $(this).val();
        const isChecked = this.checked;
        
        // Update selectedProductIds
        if (isChecked) {
            selectedProductIds.add(productId);
        } else {
            selectedProductIds.delete(productId);
        }
        
        // Update checkbox state in both lists
        $(`#product-list input[value="${productId}"], #selected-product-list input[value="${productId}"]`)
            .prop('checked', isChecked);
        
        updateSelectedProductList();
        updateCheckboxStates();
    });

    updateSelectedProductList();
});