document.addEventListener('DOMContentLoaded', function() {
    // Product data
    const products = [
        { name: 'Blueberry Check', image: 'images/blueberry-check.webp', price: '₹499' },
        { name: 'Camo', image: 'images/Camo.webp', price: '₹549' },
        { name: 'Dapper Duke', image: 'images/Dapper-Duke.webp', price: '₹599' },
        // Add all other products from your images folder
    ];

    // Load products dynamically
    const productGrid = document.querySelector('.product-grid');
    
    products.forEach(product => {
        const productItem = document.createElement('div');
        productItem.className = 'product-item';
        productItem.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <h3>${product.name}</h3>
            <p>${product.price}</p>
            <button class="btn">Add to Cart</button>
        `;
        productGrid.appendChild(productItem);
    });

    // Size chart modal functionality can be added here
});
