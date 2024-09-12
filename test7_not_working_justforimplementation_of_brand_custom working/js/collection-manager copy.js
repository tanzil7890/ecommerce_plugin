// This file is working fine with the multiple filters drag and drop where if the
// categories filter is drag and drop first then it will filter on the basis of categories
// and then if gender filter is drag and drop and at second then it will take the 
// categories filtered product then and filter it again under the gender

// And that same goes by vice versa where if the gender is drag and drop first and then 
// second drag and drop the categories. then it will first categorize first on the basis
// of gender and then the products inside the gender filter wiil again categorize on the basis of 
// categories 

//Also the brand filter is working
jQuery(document).ready(function($) {
    const filterBox = $('#filter-box');
    const filterOptions = $('#filter-options');
    const productList = $('#product-list');
    const noProductsMessage = $('#no-products-message');

    let activeFilters = [];
    let brandFilters = [];

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
        if (filterType === 'brand' || filterType === 'custom') {
            const filterName = prompt(`Enter ${filterType} name:`);
            if (filterName) {
                const filterId = `${filterType}-${Date.now()}`;
                activeFilters.push(filterId);
                if (filterType === 'brand') {
                    brandFilters.push(filterName.toLowerCase());
                }
                $(this).append(`<button type="button" class="active-filter" data-filter="${filterId}">${filterType}: ${filterName}</button>`);
                updateProductList();
            }
        } else if (!activeFilters.includes(filterType)) {
            activeFilters.push(filterType);
            $(this).append(`<button type="button" class="active-filter" data-filter="${filterType}">${filterType}</button>`);
            updateProductList();
        }
    });

    // Remove filter when clicked
    filterBox.on('click', '.active-filter', function() {
        const filterType = $(this).data('filter');
        activeFilters = activeFilters.filter(f => f !== filterType);
        if (filterType.startsWith('brand-')) {
            const brandName = $(this).text().split(': ')[1].toLowerCase();
            brandFilters = brandFilters.filter(b => b !== brandName);
        }
        $(this).remove();
        updateProductList();
    });

    function updateProductList() {
        productList.empty();

        if (activeFilters.length > 0) {
            if (brandFilters.length > 0) {
                appendBrandFilteredProducts();
            } else if (activeFilters.includes('gender') && activeFilters.includes('categories')) {
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

    function appendBrandFilteredProducts() {
        const otherBrands = [];

        brandFilters.forEach(brand => {
            const brandProducts = productData.filter(product => 
                product.categories.some(cat => categories.find(c => c.term_id === cat)?.name.toLowerCase().includes(brand)) ||
                (product.tags && product.tags.some(tag => tag.toLowerCase().includes(brand)))
            );

            if (brandProducts.length > 0) {
                let brandHtml = `
                    <div class="brand-group" data-brand="${brand}">
                        <h3>Brand Name: ${brand.charAt(0).toUpperCase() + brand.slice(1)}</h3>
                        <div class="brand-products">
                            ${brandProducts.map(product => getProductHtml(product)).join('')}
                        </div>
                    </div>
                `;
                productList.append(brandHtml);
            }

            otherBrands.push(...productData.filter(product => !brandProducts.includes(product)));
        });

        if (otherBrands.length > 0) {
            let otherBrandsHtml = `
                <div class="brand-group" data-brand="other">
                    <h3>Other Brands</h3>
                    <div class="brand-products">
                        ${otherBrands.map(product => getProductHtml(product)).join('')}
                    </div>
                </div>
            `;
            productList.append(otherBrandsHtml);
        }
    }

    function appendGenderWithCategoriesGroups() {
        const genders = ['men', 'women', 'unisex'];
        
        genders.forEach(gender => {
            const genderProducts = productData.filter(product => product.gender === gender);
            
            if (genderProducts.length > 0) {
                let genderHtml = `
                    <div class="gender-group" data-gender="${gender}">
                        <label>
                            <input type="checkbox" class="gender-checkbox" data-gender="${gender}">
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
        if (brandFilters.length > 0) {
            // Sort within each brand group
            $('.brand-group').each(function() {
                sortProductsInContainer($(this).find('.brand-products'), sortOrder);
            });
        } else if (activeFilters.includes('gender') && activeFilters.includes('categories')) {
            // Sort within each gender and then within each category
            $('.gender-group').each(function() {
                $(this).find('.category-group').each(function() {
                    sortProductsInContainer($(this).find('.category-products'), sortOrder);
                });
                sortProductsInContainer($(this).find('.gender-products'), sortOrder);
            });
        } else if (activeFilters.includes('categories')) {
            // Sort within each category
            $('.category-group').each(function() {
                sortProductsInContainer($(this).find('.category-products'), sortOrder);
            });
        } else if (activeFilters.includes('gender')) {
            // Sort within each gender group
            $('.gender-group').each(function() {
                sortProductsInContainer($(this).find('.gender-products'), sortOrder);
            });
        } else {
            // Sort all products directly
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
        $('.category-group, .gender-group, .brand-group').each(function() {
            const $group = $(this);
            const $visibleProducts = $group.find('.product-item:visible');
            $group.toggle($visibleProducts.length > 0);
        });

        if ($('.product-item:visible').length === 0) {
            noProductsMessage.show();
        } else {
            noProductsMessage.hide();
        }
    }
});