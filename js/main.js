document.addEventListener('DOMContentLoaded', function() {
    // A complete list of your products
    const products = [
        { name: 'Blueberry Check', image: 'images/blueberry-check.webp', price: '499' },
        { name: 'Camo Green', image: 'images/Camo.webp', price: '549' },
        { name: 'Dapper Duke', image: 'images/Dapper-Duke.webp', price: '599' },
        { name: 'Desert Sand', image: 'images/Desert-Sand.webp', price: '499' },
        { name: 'Ocean Blue', image: 'images/Ocean-Blue.webp', price: '529' },
        { name: 'Ruby Red', image: 'images/Ruby-Red.webp', price: '529' },
        // Add more products here if you have them
    ];

    const productGrid = document.querySelector('.product-grid');
    const cart = []; // Array to hold cart items

    // Check if the product grid exists on the page
    if (productGrid) {
        // Clear any existing content
        productGrid.innerHTML = '';

        // Dynamically create and add each product to the grid
        products.forEach(product => {
            const productItem = document.createElement('div');
            productItem.className = 'product-item';

            productItem.innerHTML = `
                <img src="${product.image}" alt="${product.name}" loading="lazy">
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <p>৳${product.price}</p>
                    <button class="btn add-to-cart-btn" data-product-name="${product.name}">Add to Cart</button>
                </div>
            `;

            productGrid.appendChild(productItem);
        });
    }

    // Add event listeners to the "Add to Cart" buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const buttonEl = event.target;
            const productName = buttonEl.dataset.productName;

            // Add the product to our cart array
            cart.push(productName);

            // Give visual feedback to the user
            buttonEl.textContent = 'Added!';
            buttonEl.style.backgroundColor = '#28a745'; // Green color

            // Optional: Revert button text after a delay
            setTimeout(() => {
                buttonEl.textContent = 'Add to Cart';
                buttonEl.style.backgroundColor = ''; // Revert to original color
            }, 2000);

            // You can view the cart contents in the browser's console
            console.log('Cart:', cart);
            alert(`${productName} has been added to your cart!`);
        });
    });
});
