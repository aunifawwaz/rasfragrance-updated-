<?php
session_start();
require_once 'db_connection.php';

// Check admin role
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$active_tab = $_GET['tab'] ?? 'stock';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$perfumes = []; // Add this line

// First, check if we need to add the perfume_sizes table
$check_table = $conn->query("SHOW TABLES LIKE 'perfume_sizes'");
if ($check_table->num_rows == 0) {
    // Create the perfume_sizes table if it doesn't exist
    $conn->query("CREATE TABLE perfume_sizes (
        size_id INT AUTO_INCREMENT PRIMARY KEY,
        perfume_id INT NOT NULL,
        size INT NOT NULL COMMENT 'Size in ml',
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        FOREIGN KEY (perfume_id) REFERENCES perfume(perfume_id) ON DELETE CASCADE
    )");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_perfume'])) {
        // Initialize all variables with proper validation
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $gender = isset($_POST['gender']) ? $_POST['gender'] : 'unisex';
        $is_seasonal = isset($_POST['is_seasonal']) ? 1 : 0;
        $is_discontinued = isset($_POST['is_discontinued']) ? 1 : 0;
        $season = isset($_POST['season']) ? $_POST['season'] : null;
        $is_all_time_favorite = isset($_POST['is_all_time_favorite']) ? 1 : 0;
        $image_path = isset($_POST['existing_image']) ? $_POST['existing_image'] : '';

        // Validate required fields
        if (empty($name)) {
            $_SESSION['message'] = "Perfume name is required!";
            header("Location: manage_perfumes.php?tab=$active_tab");
            exit();
        }

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "images/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                $new_filename = uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $new_filename;
                    if (!empty($_POST['existing_image']) && file_exists($target_dir . $_POST['existing_image'])) {
                        unlink($target_dir . $_POST['existing_image']);
                    }
                }
            }
        }

        // Database operations
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE perfume SET 
                perfume_name = ?, 
                description = ?, 
                price = ?,
                quantity = ?,
                gender = ?,
                is_seasonal = ?,
                is_discontinued = ?,
                is_all_time_favorite = ?,
                season = ?,
                image = ?,
                deleted = 0
                WHERE perfume_id = ?");
            $stmt->bind_param("ssdissiissi", 
                $name, $description, $price, $quantity, $gender, 
                $is_seasonal, $is_discontinued, $is_all_time_favorite, $season, $image_path, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO perfume 
                (perfume_name, description, price, quantity, gender, is_seasonal, is_discontinued, is_all_time_favorite, season, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdissiiss", 
                $name, $description, $price, $quantity, $gender, 
                $is_seasonal, $is_discontinued, $is_all_time_favorite, $season, $image_path);
        }
        
        // In the save_perfume handler (around line 60)
if ($stmt->execute()) {
    $perfume_id = $id > 0 ? $id : $stmt->insert_id;
    $stmt->close();
    
    // Handle sizes
    $conn->query("DELETE FROM perfume_sizes WHERE perfume_id = $perfume_id");
    
    if (isset($_POST['size'])) {
        foreach ($_POST['size'] as $index => $size) {
            $size_price = isset($_POST['price'][$index]) ? (float)$_POST['price'][$index] : 0;
            $size_quantity = isset($_POST['quantity'][$index]) ? (int)$_POST['quantity'][$index] : 0;
            
            if ($size > 0 && $size_price > 0) {
                $stmt = $conn->prepare("INSERT INTO perfume_sizes 
                    (perfume_id, size, price, quantity) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iidi", $perfume_id, $size, $size_price, $size_quantity);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    $_SESSION['message'] = "Perfume saved successfully!";
}}

    if (isset($_POST['delete_id'])) {
        // Soft delete - mark as deleted
        $stmt = $conn->prepare("UPDATE perfume SET deleted = 1 WHERE perfume_id = ?");
        $stmt->bind_param("i", $_POST['delete_id']);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Perfume removed from admin view!";
        } else {
            $_SESSION['message'] = "Error removing perfume: " . $stmt->error;
        }
        $stmt->close();
        header("Location: manage_perfumes.php?tab=$active_tab");
        exit();
    }
    
    if (isset($_POST['toggle_seasonal'])) {
        $id = (int)$_POST['id'];
        $current = $conn->query("SELECT is_seasonal FROM perfume WHERE perfume_id = $id")->fetch_assoc();
        $new_value = $current['is_seasonal'] ? 0 : 1;
        
        $conn->query("UPDATE perfume SET is_seasonal = $new_value WHERE perfume_id = $id");
        $_SESSION['message'] = "Seasonal status updated!";
        header("Location: manage_perfumes.php?tab=seasonal");
        exit();
    }
    
    if (isset($_POST['toggle_discontinued'])) {
        $id = (int)$_POST['id'];
        $current = $conn->query("SELECT is_discontinued FROM perfume WHERE perfume_id = $id")->fetch_assoc();
        $new_value = $current['is_discontinued'] ? 0 : 1;
        
        $conn->query("UPDATE perfume SET is_discontinued = $new_value WHERE perfume_id = $id");
        $_SESSION['message'] = "Discontinued status updated!";
        header("Location: manage_perfumes.php?tab=discontinued");
        exit();
    }
}

// In the column check section (around line 150 in your code), update it to:
$check_columns = $conn->query("SHOW COLUMNS FROM perfume LIKE 'gender'");
if ($check_columns->num_rows == 0) {
    // Add the new columns if they don't exist
    $conn->query("ALTER TABLE perfume 
        ADD COLUMN gender ENUM('women','men','unisex') NOT NULL DEFAULT 'unisex',
        ADD COLUMN is_seasonal TINYINT(1) NOT NULL DEFAULT 0,
        ADD COLUMN is_discontinued TINYINT(1) NOT NULL DEFAULT 0,
        ADD COLUMN is_all_time_favorite TINYINT(1) NOT NULL DEFAULT 0,
        ADD COLUMN season VARCHAR(50) NULL,
        ADD COLUMN deleted TINYINT(1) NOT NULL DEFAULT 0");
}

// Fetch perfumes based on active tab
$perfumes = [];
$query = "SELECT * FROM perfume WHERE deleted = 0";

switch ($active_tab) {
    case 'women':
        $query .= " AND gender = 'women'";
        break;
    case 'men':
        $query .= " AND gender = 'men'";
        break;
    case 'discontinued':
        $query .= " AND is_discontinued = 1";
        break;
    case 'seasonal':
        $query .= " AND is_seasonal = 1";
        break;
    default: // 'stock'
        $query .= " AND is_discontinued = 0";
        break;
}

$query .= " ORDER BY perfume_name";

$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$perfumes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get sizes for each perfume
        $sizes_result = $conn->query("SELECT * FROM perfume_sizes WHERE perfume_id = {$row['perfume_id']} ORDER BY size");
        if (!$sizes_result) {
            die("Sizes query failed: " . $conn->error);
        }
        
        $row['sizes'] = [];
        while ($size_row = $sizes_result->fetch_assoc()) {
            $row['sizes'][] = $size_row;
        }
        $perfumes[] = $row;
    }
}

// Prepare for edit
$editing_perfume = [
    'perfume_id' => 0,
    'perfume_name' => '',
    'description' => '',
    'price' => 0,
    'quantity' => 0,
    'gender' => 'unisex',
    'is_seasonal' => 0,
    'is_discontinued' => 0,
    'season' => null,
    'image' => '',
    'sizes' => []
];

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach ($perfumes as $p) {
        if ($p['perfume_id'] == $edit_id) {
            $editing_perfume = $p;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Perfumes - RAS Fragrance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8C3F49;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .nav-tabs .nav-link {
            color: var(--secondary);
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            font-weight: 600;
            border-bottom: 3px solid var(--primary);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .perfume-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .badge-women {
            background-color: #ff99cc;
            color: #000;
        }
        
        .badge-men {
            background-color: #99ccff;
            color: #000;
        }
        
        .badge-unisex {
            background-color: #cc99ff;
            color: #000;
        }
        
        .badge-seasonal {
            background-color: #ffcc66;
            color: #000;
        }
        
        .badge-discontinued {
            background-color: #ff6666;
            color: #000;
        }
        
        .badge-deleted {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-stock-high {
            background-color: #198754;
            color: white;
        }
        
        .badge-stock-low {
            background-color: #ffc107;
            color: #000;
        }
        
        .badge-stock-out {
            background-color: #dc3545;
            color: white;
        }
        
        .status-toggle {
            cursor: pointer;
        }
        .sizes-container {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 8px 12px;
}

        .size-item {
            border-bottom: 1px solid #e9ecef;
            padding: 6px 0;
        }

        .size-item:last-child {
            border-bottom: none;
        }

        .size-label {
            font-weight: 600;
            color: var(--primary);
            width: 60px;
        }

        .price {
            font-weight: 500;
            flex-grow: 1;
            text-align: center;
        }

        .stock-badge {
            font-size: 0.8rem;
            min-width: 80px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include the menu from separate file -->
            <?php 
            $current_page = 'manage_perfumes.php';
            include 'admin_menu.php'; 
            ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">Manage Perfumes</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#perfumeModal">
                            <i class="fas fa-plus"></i> Add Perfume
                        </button>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'stock' ? 'active' : '' ?>" 
                           href="?tab=stock">All Perfumes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'women' ? 'active' : '' ?>" 
                           href="?tab=women">Women</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'men' ? 'active' : '' ?>" 
                           href="?tab=men">Men</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'seasonal' ? 'active' : '' ?>" 
                           href="?tab=seasonal">Seasonal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'discontinued' ? 'active' : '' ?>" 
                           href="?tab=discontinued">Discontinued</a>
                    </li>
                </ul>

                <!-- Perfumes List -->
                <div class="row">
                    <?php foreach ($perfumes as $perfume): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if (!empty($perfume['image'])): ?>
                                    <img src="<?= htmlspecialchars($perfume['image']) ?>" class="card-img-top perfume-img" alt="<?= htmlspecialchars($perfume['perfume_name']) ?>">
                                <?php else: ?>
                                    <div class="card-img-top perfume-img bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-wine-bottle fa-5x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($perfume['perfume_name']) ?></h5>
                                    <p class="card-text text-muted"><?= htmlspecialchars(substr($perfume['description'], 0, 100)) ?>...</p>
                                    
                                    <!-- Gender Badge -->
                                    <div class="mb-2">
                                        <span class="badge rounded-pill 
                                            <?= $perfume['gender'] === 'women' ? 'badge-women' : '' ?>
                                            <?= $perfume['gender'] === 'men' ? 'badge-men' : '' ?>
                                            <?= $perfume['gender'] === 'unisex' ? 'badge-unisex' : '' ?>">
                                            <?= ucfirst($perfume['gender']) ?>
                                        </span>
                                        
                                        <?php if ($perfume['is_seasonal']): ?>
                                            <span class="badge rounded-pill badge-seasonal">Seasonal</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($perfume['is_discontinued']): ?>
                                            <span class="badge rounded-pill badge-discontinued">Discontinued</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Size and Price Listing -->
                                    <div class="sizes-container mb-2">
                                        <?php 
                                        $total_quantity = 0;
                                        foreach ($perfume['sizes'] as $size): 
                                            $total_quantity += $size['quantity'];
                                        ?>
                                            <div class="size-item d-flex justify-content-between align-items-center py-1">
                                                <span class="size-label"><?= $size['size'] ?>ml:</span>
                                                <span class="price">RM <?= number_format($size['price'], 2) ?></span>
                                                <span class="stock-badge badge <?= $size['quantity'] > 20 ? 'bg-success' : ($size['quantity'] > 0 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                                    Stock: <?= $size['quantity'] ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Total Stock -->
                                    <div class="total-stock mb-2">
                                        <span class="badge rounded-pill bg-primary">
                                            Total Stock: <?= $total_quantity ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-flex justify-content-between">
                                        <a href="#" class="btn btn-sm btn-outline-primary edit-perfume-btn"
                                           data-id="<?= $perfume['perfume_id'] ?>"
                                           data-name="<?= htmlspecialchars($perfume['perfume_name']) ?>"
                                           data-description="<?= htmlspecialchars($perfume['description']) ?>"
                                           data-price="<?= $perfume['price'] ?>"
                                           data-quantity="<?= $perfume['quantity'] ?>"
                                           data-gender="<?= $perfume['gender'] ?>"
                                           data-is_seasonal="<?= $perfume['is_seasonal'] ?>"
                                           data-is_discontinued="<?= $perfume['is_discontinued'] ?>"
                                           data-season="<?= $perfume['season'] ?>"
                                           data-is_all_time_favorite="<?= $perfume['is_all_time_favorite'] ?>"
                                           data-image_path="<?= htmlspecialchars($perfume['image']) ?>"
                                           data-sizes='<?= json_encode($perfume['sizes']) ?>'>
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        
                                        <form method="post" class="d-inline" onsubmit="return confirm('Remove this perfume from admin view?')">
                                            <input type="hidden" name="delete_id" value="<?= $perfume['perfume_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <?php if ($active_tab === 'seasonal'): ?>
                                        <form method="post" class="mt-2">
                                            <input type="hidden" name="id" value="<?= $perfume['perfume_id'] ?>">
                                            <button type="submit" name="toggle_seasonal" class="btn btn-sm btn-outline-warning w-100">
                                                <i class="fas fa-toggle-<?= $perfume['is_seasonal'] ? 'on' : 'off' ?>"></i>
                                                <?= $perfume['is_seasonal'] ? 'Disable Seasonal' : 'Enable Seasonal' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($active_tab === 'discontinued'): ?>
                                        <form method="post" class="mt-2">
                                            <input type="hidden" name="id" value="<?= $perfume['perfume_id'] ?>">
                                            <button type="submit" name="toggle_discontinued" class="btn btn-sm btn-outline-danger w-100">
                                                <i class="fas fa-toggle-<?= $perfume['is_discontinued'] ? 'on' : 'off' ?>"></i>
                                                <?= $perfume['is_discontinued'] ? 'Restore Product' : 'Mark Discontinued' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($perfumes)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center py-4">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h4>No perfumes found</h4>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

<!-- Perfume Modal Form -->
<div class="modal fade" id="perfumeModal" tabindex="-1" aria-labelledby="perfumeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="perfumeModalLabel">Add/Edit Perfume</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editPerfumeId" value="0">
                    <input type="hidden" name="existing_image" id="editExistingImage" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Perfume Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" id="editGender" class="form-select" required>
                                <option value="unisex">Unisex</option>
                                <option value="women">Women</option>
                                <option value="men">Men</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <!-- Sizes Section -->
                    <div class="mb-3">
                        <label class="form-label">Available Sizes</label>
                        <div id="sizesContainer">
                            <!-- Size rows will be added here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addSizeBtn">
                            <i class="fas fa-plus"></i> Add Size
                        </button>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_seasonal" id="editIsSeasonal">
                                <label class="form-check-label" for="editIsSeasonal">
                                    Seasonal Product
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_all_time_favorite" id="editIsAllTimeFavorite">
                                <label class="form-check-label" for="editIsAllTimeFavorite">
                                    All Time Favorite
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_discontinued" id="editIsDiscontinued">
                                <label class="form-check-label" for="editIsDiscontinued">
                                    Discontinued
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="seasonField" style="display: none;">
                        <label class="form-label">Season</label>
                        <select name="season" id="editSeason" class="form-select">
                            <option value="spring">Spring</option>
                            <option value="summer">Summer</option>
                            <option value="autumn">Autumn</option>
                            <option value="winter">Winter</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" id="editImage" class="form-control" accept="image/*">
                        <small class="text-muted">Leave blank to keep existing image</small>
                        <div class="mt-2" id="imagePreviewContainer">
                            <!-- Image preview will be shown here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_perfume" class="btn btn-primary">Save Perfume</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize modal
    const perfumeModal = new bootstrap.Modal(document.getElementById('perfumeModal'));
        
    // Update the addSizeRow function (around line 650)
function addSizeRow(size = '', price = '', quantity = '') {
    const container = document.getElementById('sizesContainer');
    const rowId = Date.now();
    
    const row = document.createElement('div');
    row.className = 'row mb-2 size-row';
    row.dataset.rowId = rowId;
    
    row.innerHTML = `
        <div class="col-md-3">
            <select name="size[]" class="form-select size-select" required>
                <option value="">Select Size</option>
                <option value="10" ${size == 10 ? 'selected' : ''}>10ml</option>
                <option value="30" ${size == 30 ? 'selected' : ''}>30ml</option>
                <option value="50" ${size == 50 ? 'selected' : ''}>50ml</option>
                <option value="100" ${size == 100 ? 'selected' : ''}>100ml</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" step="0.01" name="price[]" class="form-control" 
                   placeholder="Price" value="${price}" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="quantity[]" class="form-control" 
                   placeholder="Quantity" value="${quantity}" min="0" required>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-sm btn-outline-danger remove-size-btn" data-row-id="${rowId}">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.appendChild(row);
}
    
    // Add size button handler
    document.getElementById('addSizeBtn').addEventListener('click', function() {
        addSizeRow();
    });
    
    // Remove size button handler (using event delegation)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-size-btn') || e.target.closest('.remove-size-btn')) {
            const btn = e.target.classList.contains('remove-size-btn') ? e.target : e.target.closest('.remove-size-btn');
            const rowId = btn.dataset.rowId;
            document.querySelector(`.size-row[data-row-id="${rowId}"]`).remove();
        }
    });
    
    // Toggle season field based on seasonal checkbox
    document.getElementById('editIsSeasonal').addEventListener('change', function() {
        document.getElementById('seasonField').style.display = this.checked ? 'block' : 'none';
    });
    
    // Handle image preview
    document.getElementById('editImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('imagePreviewContainer');
                preview.innerHTML = `<img src="${event.target.result}" class="img-thumbnail mt-2" style="max-height: 150px;">`;
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Edit perfume button handler
    document.querySelectorAll('.edit-perfume-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editPerfumeId').value = this.dataset.id;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editDescription').value = this.dataset.description;
            document.getElementById('editGender').value = this.dataset.gender;
            document.getElementById('editIsSeasonal').checked = this.dataset.is_seasonal === '1';
            document.getElementById('editIsDiscontinued').checked = this.dataset.is_discontinued === '1';
            document.getElementById('editSeason').value = this.dataset.season || 'spring';
            document.getElementById('editExistingImage').value = this.dataset.image_path || '';
            document.getElementById('editIsAllTimeFavorite').checked = this.dataset.is_all_time_favorite === '1';
            
            // Clear existing sizes
            document.getElementById('sizesContainer').innerHTML = '';
            
            try {
                // Add size rows from data attributes
                const sizes = JSON.parse(this.dataset.sizes || '[]');
                if (sizes.length > 0) {
                    sizes.forEach(size => {
                        addSizeRow(size.size, size.price, size.quantity);
                    });
                } else {
                    // Add default empty size row
                    addSizeRow();
                }
            } catch (e) {
                console.error("Error parsing sizes:", e);
                addSizeRow();
            }
            
            // Show/hide season field
            document.getElementById('seasonField').style.display = 
                this.dataset.is_seasonal === '1' ? 'block' : 'none';
            
            // Show existing image preview if available
            const preview = document.getElementById('imagePreviewContainer');
            if (this.dataset.image_path) {
                preview.innerHTML = `<img src="${this.dataset.image_path}" class="img-thumbnail mt-2" style="max-height: 150px;">`;
            } else {
                preview.innerHTML = '';
            }
            
            perfumeModal.show();
        });
    });
    
    // New perfume button handler
document.querySelector('[data-bs-target="#perfumeModal"]').addEventListener('click', function() {
    document.getElementById('editPerfumeId').value = '0';
    document.getElementById('editName').value = '';
    document.getElementById('editDescription').value = '';
    document.getElementById('editGender').value = 'unisex';
    document.getElementById('editIsSeasonal').checked = false;
    document.getElementById('editIsDiscontinued').checked = false;
    document.getElementById('editIsAllTimeFavorite').checked = false;
    document.getElementById('editSeason').value = 'spring';
    document.getElementById('editExistingImage').value = '';
    document.getElementById('seasonField').style.display = 'none';
    document.getElementById('imagePreviewContainer').innerHTML = '';
    document.getElementById('sizesContainer').innerHTML = '';
    
    // Add default size rows
    addSizeRow(10);
    addSizeRow(30);
});
</script>
</body>
</html>