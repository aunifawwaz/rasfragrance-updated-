// This should be in a script tag or external JS file
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count when adding items via AJAX
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.classList.contains('ajax-add')) {
                e.preventDefault();
                const form = this.closest('form');
                const formData = new FormData(form);
                
                fetch('add_to_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart counter
                        const cartCounter = document.querySelector('.cart-count');
                        if (data.count > 0) {
                            if (cartCounter) {
                                cartCounter.textContent = data.count;
                            } else {
                                const counterSpan = document.createElement('span');
                                counterSpan.className = 'cart-count';
                                counterSpan.textContent = data.count;
                                document.querySelector('.fa-shopping-bag').parentNode.appendChild(counterSpan);
                            }
                        } else if (cartCounter) {
                            cartCounter.remove();
                        }
                    }
                });
            }
        });
    });
});