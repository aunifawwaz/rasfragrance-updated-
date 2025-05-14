<?php
// Force no caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Start session and verify admin
session_start();

// Include database connection
include 'db_connection.php';

// Set current page for menu highlighting
$current_page = 'manage_blog.php';

// Initialize variables
$message = '';
$blogs = [];
$edit_blog_id = null; // Initialize edit_blog_id
$blog_to_edit = null; // Initialize blog_to_edit

// Check for session message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Check if we're editing a blog
if (isset($_GET['edit_id'])) {
    $edit_blog_id = (int)$_GET['edit_id'];
} elseif (isset($_POST['edit_blog'])) {
    $edit_blog_id = (int)$_POST['id'];
}
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_blog'])) {
        // Update existing blog
        $id = (int)$_POST['id'];
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);
        
        // Handle image upload
        $image_path = $_POST['existing_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "images/";
            $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            
            // Validate image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                $new_filename = uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                    // Delete old image if not placeholder
                    if ($_POST['existing_image'] != 'images/placeholder.png' && file_exists($_POST['existing_image'])) {
                        unlink($_POST['existing_image']);
                    }
                }
            }
        }
        
        $query = "UPDATE blogs SET title='$title', content='$content', image='$image_path' WHERE id=$id";
        if ($conn->query($query)) {
            $_SESSION['message'] = "Blog updated successfully!";
            header("Location: manage_blog.php");
            exit;
        } else {
            $_SESSION['message'] = "Error updating blog: " . $conn->error;
            header("Location: manage_blog.php");
            exit;
        }
    } 
    elseif (isset($_POST['add_blog'])) {
        // Add new blog
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);
        $image_path = 'images/placeholder.png';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "images/";
            $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                $new_filename = uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                }
            }
        }
        
        // Check for duplicate title
        $check_query = "SELECT id FROM blogs WHERE title = '$title' LIMIT 1";
        $check_result = $conn->query($check_query);
        
        if ($check_result && $check_result->num_rows > 0) {
            $_SESSION['message'] = "A blog with this title already exists!";
            header("Location: manage_blog.php");
            exit;
        } else {
            $query = "INSERT INTO blogs (title, content, image, created_at) VALUES ('$title', '$content', '$image_path', NOW())";
            if ($conn->query($query)) {
                $_SESSION['message'] = "New blog added successfully!";
                header("Location: manage_blog.php");
                exit;
            } else {
                $_SESSION['message'] = "Error adding blog: " . $conn->error;
                header("Location: manage_blog.php");
                exit;
            }
        }
    } 
    elseif (isset($_POST['delete_blog'])) {
        // Delete blog
        $id = (int)$_POST['id'];
        
        // First get the image path to delete the file
        $query = "SELECT image FROM blogs WHERE id=$id";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['image'] != 'images/placeholder.png' && file_exists($row['image'])) {
                unlink($row['image']);
            }
        }
        
        $query = "DELETE FROM blogs WHERE id=$id";
        if ($conn->query($query)) {
            $_SESSION['message'] = "Blog deleted successfully!";
            header("Location: manage_blog.php");
            exit;
        } else {
            $_SESSION['message'] = "Error deleting blog: " . $conn->error;
            header("Location: manage_blog.php");
            exit;
        }
    }
}

// Fetch all blogs for listing
$query = "SELECT * FROM blogs ORDER BY created_at DESC";
$result = $conn->query($query);

if ($result) {
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
} else {
    $message = "Error fetching blogs: " . $conn->error;
}

// Fetch the specific blog being edited if applicable
$blog_to_edit = null;
if ($edit_blog_id) {
    $query = "SELECT * FROM blogs WHERE id = $edit_blog_id LIMIT 1";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $blog_to_edit = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog - Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
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
        
        .sidebar {
            min-height: 100vh;
            background: var(--dark);
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 0.75rem 1rem;
            margin-bottom: 0.2rem;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        .main-content {
            padding: 20px;
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
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .page-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin: 10px 0;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        textarea {
            height: 150px;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            background: #ddd;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }
        
        .tab.active {
            background: var(--primary);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include the admin menu -->
            <?php include 'admin_menu.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 page-header">
                    <h1 class="h2">Manage Blog Posts</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= strpos($message, 'Error') === false ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Blog Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="tabs">
                            <div class="tab <?= !$edit_blog_id ? 'active' : '' ?>" onclick="openTab('existing-blogs')">Existing Blogs</div>
                            <div class="tab" onclick="openTab('add-blog')">Add New Blog</div>
                        </div>
                        
                        <div id="existing-blogs" class="tab-content <?= !$edit_blog_id ? 'active' : '' ?>">
                            <?php if (count($blogs) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Image</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($blogs as $blog): ?>
                                                <tr>
                                                    <td><?= $blog['id'] ?></td>
                                                    <td><?= htmlspecialchars($blog['title']) ?></td>
                                                    <td>
                                                        <?php if (!empty($blog['image'])): ?>
                                                            <img src="<?= htmlspecialchars($blog['image']) ?>" alt="Blog Image" class="preview-image">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('M j, Y g:i a', strtotime($blog['created_at'])) ?></td>
                                                    <td>
                                                        <form method="post" style="display:inline;">
                                                            <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                                            <button type="submit" name="edit_blog" class="btn btn-sm btn-primary btn-action">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                        </form>
                                                        <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this blog?');">
                                                            <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                                            <button type="submit" name="delete_blog" class="btn btn-sm btn-danger btn-action">
                                                                <i class="fas fa-trash-alt"></i> Delete
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">No blog posts found.</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Edit Blog Form (now separate from the table) -->
                        <?php if ($blog_to_edit): ?>
                        <div id="edit-blog" class="tab-content active">
                            <form method="post" enctype="multipart/form-data" class="mt-3">
                                <h4>Edit Blog Post</h4>
                                <input type="hidden" name="id" value="<?= $blog_to_edit['id'] ?>">
                                <input type="hidden" name="existing_image" value="<?= $blog_to_edit['image'] ?>">
                                
                                <div class="form-group">
                                    <label for="title">Title:</label>
                                    <input type="text" name="title" value="<?= htmlspecialchars($blog_to_edit['title']) ?>" required class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="content">Content:</label>
                                    <textarea name="content" required class="form-control" rows="6"><?= htmlspecialchars($blog_to_edit['content']) ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">Image:</label>
                                    <?php if (!empty($blog_to_edit['image'])): ?>
                                        <img src="<?= htmlspecialchars($blog_to_edit['image']) ?>" alt="Current Image" class="preview-image"><br>
                                    <?php endif; ?>
                                    <input type="file" name="image" accept="image/*" class="form-control">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>
                                
                                <button type="submit" name="update_blog" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Blog
                                </button>
                                <a href="manage_blog.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </form>
                        </div>
                        <?php endif; ?>
                        
                        <div id="add-blog" class="tab-content">
                            <!-- [Your existing add blog form remains the same] -->
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show the selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Update active tab
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Find the tab that matches our tabId
            document.querySelectorAll('.tab').forEach(tab => {
                if (tab.textContent.toLowerCase().includes(tabId.replace('-', ' '))) {
                    tab.classList.add('active');
                }
            });
        }

        // Disable form buttons after submission
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const buttons = this.querySelectorAll('button[type="submit"]');
                    buttons.forEach(button => {
                        button.disabled = true;
                        if (button.innerHTML.includes('fa-plus')) {
                            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                        } else if (button.innerHTML.includes('fa-save')) {
                            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                        } else if (button.innerHTML.includes('fa-trash-alt')) {
                            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>