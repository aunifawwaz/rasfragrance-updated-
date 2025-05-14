<?php
// Start the session
session_start();

// Include database connection
include 'db_connection.php';

// Set content type to JSON
header('Content-Type: application/json');

// Validate and get perfume_id from GET parameter
$perfume_id = isset($_GET['perfume_id']) ? intval($_GET['perfume_id']) : 0;

if ($perfume_id <= 0) {
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'Invalid perfume ID']);
    exit();
}

try {
    // Prepare and execute query to get available sizes
    $stmt = $conn->prepare("SELECT 
                            size_id, 
                            size, 
                            price, 
                            quantity 
                          FROM perfume_sizes 
                          WHERE perfume_id = ? AND quantity > 0
                          ORDER BY size ASC");
    $stmt->bind_param("i", $perfume_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sizes = [];
    while ($row = $result->fetch_assoc()) {
        $sizes[] = [
            'size_id' => $row['size_id'],
            'size' => $row['size'],
            'price' => (float)$row['price'],
            'quantity' => $row['quantity']
        ];
    }
    
    if (empty($sizes)) {
        http_response_code(404); // Not found
        echo json_encode(['error' => 'No available sizes for this perfume']);
        exit();
    }
    
    // Return sizes as JSON
    echo json_encode($sizes);
    
} catch (Exception $e) {
    http_response_code(500); // Internal server error
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Size Selection</title>
    <style>
        /* Modal Styles */
        .size-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 25px;
            border-radius: 8px;
            width: 350px;
            max-width: 90%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #333;
        }

        .modal-title {
            margin-top: 0;
            color: #8C3F49;
            font-size: 1.5rem;
        }

        /* Size Options */
        .size-options {
            margin: 20px 0;
        }

        .size-option {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .size-option:hover {
            border-color: #8C3F49;
            background-color: #f9f2f3;
        }

        .size-option.selected {
            border-color: #8C3F49;
            background-color: #f9f2f3;
        }

        .size-option input[type="radio"] {
            margin-right: 15px;
            accent-color: #8C3F49;
        }

        .size-info {
            flex-grow: 1;
        }

        .size-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .size-price {
            color: #8C3F49;
            font-weight: bold;
        }

        /* Quantity Selector */
        .quantity-selector {
            margin: 20px 0;
        }

        .quantity-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .quantity-input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        /* Add to Cart Button */
        .add-to-cart-btn {
            background-color: #8C3F49;
            color: white;
            border: none;
            padding: 12px 20px;
            width: 100%;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-to-cart-btn:hover {
            background-color: #a64d4d;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            border-top: 4px solid #8C3F49;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Error Message */
        .error-message {
            color: #dc3545;
            text-align: center;
            padding: 15px;
        }
    </style>
</head>
<body>
    <!-- Example Product Card (this would be in your products.php) -->
    <div class="product-card">
        <h3>Example Perfume</h3>
        <button onclick="openSizeModal(123)">Select Size</button>
    </div>

    <!-- Size Selection Modal -->
    <div id="sizeModal" class="size-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3 class="modal-title">Select Size</h3>
            
            <div id="modalLoading" class="loading">
                <div class="spinner"></div>
                <p>Loading sizes...</p>
            </div>
            
            <div id="sizeSelection" style="display:none;">
                <form id="sizeForm" method="POST" action="cart.php">
                    <input type="hidden" name="perfume_id" id="modalPerfumeId">
                    
                    <div class="size-options" id="sizeOptionsContainer">
                        <!-- Size options will be inserted here by JavaScript -->
                    </div>
                    
                    <div class="quantity-selector">
                        <label for="quantity" class="quantity-label">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" 
                               class="quantity-input" value="1" min="1" max="10" required>
                    </div>
                    
                    <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                </form>
            </div>
            
            <div id="modalError" class="error-message" style="display:none;">
                Could not load size options. Please try again.
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('sizeModal');
        const closeBtn = document.querySelector('.close-modal');
        
        // Open modal with specific perfume ID
        function openSizeModal(perfumeId) {
            document.getElementById('modalPerfumeId').value = perfumeId;
            document.getElementById('modalLoading').style.display = 'block';
            document.getElementById('sizeSelection').style.display = 'none';
            document.getElementById('modalError').style.display = 'none';
            
            modal.style.display = 'block';
            
            // Fetch sizes from server
            fetch(`get_sizes.php?perfume_id=${perfumeId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(sizes => {
                    if (!sizes || sizes.length === 0) throw new Error('No sizes available');
                    
                    const container = document.getElementById('sizeOptionsContainer');
                    container.innerHTML = '';
                    
                    sizes.forEach(size => {
                        const option = document.createElement('label');
                        option.className = 'size-option';
                        option.innerHTML = `
                            <input type="radio" name="size_id" value="${size.size_id}" required>
                            <div class="size-info">
                                <div class="size-name">${size.size}ml</div>
                                <div class="size-price">RM ${size.price.toFixed(2)}</div>
                            </div>
                        `;
                        container.appendChild(option);
                    });
                    
                    // Show size selection
                    document.getElementById('modalLoading').style.display = 'none';
                    document.getElementById('sizeSelection').style.display = 'block';
                    
                    // Add click handlers for size options
                    document.querySelectorAll('.size-option').forEach(option => {
                        option.addEventListener('click', function() {
                            const radio = this.querySelector('input[type="radio"]');
                            radio.checked = true;
                            document.querySelectorAll('.size-option').forEach(opt => {
                                opt.classList.remove('selected');
                            });
                            this.classList.add('selected');
                        });
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modalLoading').style.display = 'none';
                    document.getElementById('modalError').style.display = 'block';
                });
        }
        
        // Close modal
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close when clicking outside modal
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Form submission
        document.getElementById('sizeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Here you would typically submit via AJAX
            // For this example, we'll just close the modal
            alert('Item added to cart!');
            modal.style.display = 'none';
            
            // In your actual implementation, you might do:
            /*
            fetch('cart.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => {
                // Handle response
                modal.style.display = 'none';
                updateCartCount(); // Refresh cart count
            });
            */
        });
    </script>
</body>
</html>