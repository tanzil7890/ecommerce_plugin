To DO: Just Test:

- prod.id  with new hash array if present then don't count the same prod.
- Amazon dataset(can be old) statistical, and product data testing.
- Check this function for mismatch filtering
```function appendGenderGroups() {
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
    }```

- Look for the new reactivation.js (Also the collection restore is working fine): So exclude this for now. And for later changes 
just look at the Data Flow diagram which will help in storing the collection as in the database.


### Next to do and check:

- brand filter: User can input what brand name they want to use it for filter the brand name. After filtering with brand name it will create a new list header with the name Brand Name: ....... (brand name which you want to filter it as in the blank).
