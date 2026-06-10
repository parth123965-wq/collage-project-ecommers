<?php
// admin_dashboard.php
session_start();
require_once '../config/db_connect.php';

// Access Control Protection
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>Access Denied. Administrators Only.</h2>");
}

$message = "";
$msgColor = "var(--primary-gold)";

// Handle Post Submissions (Insert, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- CATEGORY MANIPULATION ---
    if ($action === 'add_category') {
        $catName = trim($_POST['CategoryName'] ?? '');
        $catDesc = trim($_POST['CategoryDescription'] ?? '');
        if (!empty($catName)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO Category (CategoryName, CategoryDescription) VALUES (?, ?)");
                $stmt->execute([$catName, $catDesc]);
                $message = "Category added successfully!";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $msgColor = "#FF3333";
            }
        }
    }

    if ($action === 'update_category') {
        $catId = intval($_POST['CategoryId'] ?? 0);
        $catName = trim($_POST['CategoryName'] ?? '');
        $catDesc = trim($_POST['CategoryDescription'] ?? '');
        if ($catId > 0 && !empty($catName)) {
            try {
                $stmt = $pdo->prepare("UPDATE Category SET CategoryName = ?, CategoryDescription = ? WHERE CategoryId = ?");
                $stmt->execute([$catName, $catDesc, $catId]);
                $message = "Category updated successfully!";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $msgColor = "#FF3333";
            }
        }
    }

    if ($action === 'delete_category') {
        $catId = intval($_POST['CategoryId'] ?? 0);
        if ($catId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM Category WHERE CategoryId = ?");
                $stmt->execute([$catId]);
                $message = "Category purged successfully!";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $msgColor = "#FF3333";
            }
        }
    }

    // --- PRODUCT INVENTORY MANIPULATION WITH IMAGE UPLOADS ---
    if ($action === 'add_product') {
        $pName = trim($_POST['productName'] ?? '');
        $pDesc = trim($_POST['productDescription'] ?? '');
        $pPrice = floatval($_POST['productPrice'] ?? 0);
        $pQty = intval($_POST['productQuantity'] ?? 0);
        $catId = intval($_POST['CategoryId'] ?? null);

        // Default image filename fallback
        $pImgName = 'default.jpg';

        // Check if an image was actually browsed and uploaded
        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['productImage']['tmp_name'];
            $fileName = $_FILES['productImage']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Whitelist safe extensions
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                // Generate a unique file name to avoid overwriting existing uploads
                $pImgName = time() . '_' . uniqid() . '.' . $fileExtension;

                // Destination path matching your directory tree structure: Sem-5-Project/uploads/
                $uploadFileDir = '../uploads/';

                // Automatically create directory if it doesn't exist yet
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $dest_path = $uploadFileDir . $pImgName;

                if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                    $message = "Warning: Failed to move uploaded file. Used default image.";
                    $pImgName = 'default.jpg';
                }
            } else {
                $message = "Invalid file type. Only JPG, PNG, GIF, and WEBP allowed.";
                $msgColor = "#FF3333";
            }
        }

        if (!empty($pName) && $msgColor !== "#FF3333") {
            try {
                $stmt = $pdo->prepare("INSERT INTO Product (productName, productDescription, productPrice, productQuantity, productImage, CategoryId) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$pName, $pDesc, $pPrice, $pQty, $pImgName, $catId ?: null]);
                $message = "Product registered and image uploaded successfully!";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $msgColor = "#FF3333";
            }
        }
    }

    if ($action === 'update_product') {
        $pId = intval($_POST['productId'] ?? 0);
        $pQty = intval($_POST['productQuantity'] ?? 0);

        if ($pId > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE Product SET productQuantity = ? WHERE productId = ?");
                $stmt->execute([$pQty, $pId]);
                $message = "Product stock inventory updated successfully!";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $msgColor = "#FF3333";
            }
        }
    }

    if ($action === 'delete_product') {
        $pId = intval($_POST['productId'] ?? 0);
        if ($pId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM Product WHERE productId = ?");
                $stmt->execute([$pId]);
                $message = "Product removed from database stock records.";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $msgColor = "#FF3333";
            }
        }
    }
}

// Fetch records for display updates
$categories = $pdo->query("SELECT * FROM Category ORDER BY CategoryId ASC")->fetchAll();
$products = $pdo->query("SELECT p.*, c.CategoryName FROM Product p LEFT JOIN Category c ON p.CategoryId = c.CategoryId ORDER BY p.productId ASC")->fetchAll();
$users = $pdo->query("SELECT userId, userName, email, role FROM User ORDER BY userId ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Executive Control Hub | Control Panel</title>
    <style>
        :root {
            --primary-gold: #D4AF37;
            --dark-bg: #111;
            --card-bg: #1A1A1A;
            --field-bg: #262626;
            --text-light: #F5F5F5;
            --text-muted: #A0A0A0;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            margin: 0;
            padding: 30px;
        }

        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--primary-gold);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary-gold);
            margin: 0;
            font-size: 28px;
        }

        .exit-btn {
            background: transparent;
            border: 1px solid var(--primary-gold);
            color: var(--primary-gold);
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s;
        }

        .exit-btn:hover {
            background: var(--primary-gold);
            color: var(--dark-bg);
        }

        .alert {
            background-color: #222;
            border-left: 4px solid var(--primary-gold);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .panel {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #333;
        }

        .panel h2 {
            margin-top: 0;
            color: var(--primary-gold);
            font-size: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        label {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        input,
        textarea,
        select {
            background-color: var(--field-bg);
            border: 1px solid #444;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
        }

        input[type="file"] {
            background: none;
            border: none;
            padding: 5px 0;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary-gold);
            outline: none;
        }

        .btn {
            background-color: var(--primary-gold);
            color: var(--dark-bg);
            border: none;
            padding: 11px 20px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn:hover {
            background-color: #f3cb46;
        }

        .btn-danger {
            background-color: #c0392b;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-danger:hover {
            background-color: #e74c3c;
        }

        .table-section {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #333;
            margin-bottom: 40px;
        }

        .table-section h2 {
            color: var(--primary-gold);
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #333;
            vertical-align: middle;
        }

        th {
            color: var(--text-muted);
            background-color: #222;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #222;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-low {
            background-color: #d35400;
            color: #fff;
        }

        .badge-out {
            background-color: #c0392b;
            color: #fff;
        }

        .inline-form {
            display: inline;
        }

        .prod-thumb {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #444;
            margin-right: 10px;
        }

        .flex-td {
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <h1>⚙️ Luxury Hub Management System</h1>
            <a href="../api/logout.php" class="exit-btn">Secure Logout</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert" style="border-left-color: <?php echo $msgColor; ?>; color: <?php echo $msgColor; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <div class="panel">
                <h2>📁 Category Infrastructure</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_category">
                    <div class="form-group">
                        <label>Category Title Name (New Entry)</label>
                        <input type="text" name="CategoryName" required placeholder="e.g., Premium Watches">
                    </div>
                    <div class="form-group">
                        <label>Scope Description</label>
                        <textarea name="CategoryDescription" rows="2"
                            placeholder="Describe category item classifications..."></textarea>
                    </div>
                    <button type="submit" class="btn">Save New Category</button>
                </form>

                <hr style="border: 0; border-top: 1px solid #333; margin: 25px 0;">

                <form method="POST">
                    <input type="hidden" name="action" value="update_category">
                    <div class="form-group">
                        <label>Select Target Category to Modify</label>
                        <select name="CategoryId" required>
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['CategoryId']; ?>">
                                    <?php echo htmlspecialchars($c['CategoryName']); ?> (ID:
                                    #<?php echo $c['CategoryId']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Revised Title Name</label>
                        <input type="text" name="CategoryName" required placeholder="Enter updated name">
                    </div>
                    <div class="form-group">
                        <label>Revised Description Text</label>
                        <textarea name="CategoryDescription" rows="2"
                            placeholder="Enter updated descriptions..."></textarea>
                    </div>
                    <button type="submit" class="btn" style="background-color: #2980b9; color: white;">Apply Category
                        Changes</button>
                </form>
            </div>

            <div class="panel">
                <h2>💎 Vault Product Stock Records</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="productName" required>
                        </div>
                        <div class="form-group">
                            <label>Category Group Placement</label>
                            <select name="CategoryId">
                                <option value="">General / Standalone</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?php echo $c['CategoryId']; ?>">
                                        <?php echo htmlspecialchars($c['CategoryName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Base Price ($)</label>
                            <input type="number" step="0.01" name="productPrice" required>
                        </div>
                        <div class="form-group">
                            <label>Initial Stock Allotment</label>
                            <input type="number" name="productQuantity" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Browse Product Image *</label>
                        <input type="file" name="productImage" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label>Specifications / Description Details</label>
                        <textarea name="productDescription" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn">Register New Vault Asset</button>
                </form>
            </div>
        </div>

        <div class="table-section">
            <h2>📦 Current Live Products Catalog</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Qty Status</th>
                        <th>In-line Record Adjustments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>#<?php echo $p['productId']; ?></td>
                            <td>
                                <div class="flex-td">
                                    <img src="../uploads/<?php echo htmlspecialchars($p['productImage'] ?: 'default.jpg'); ?>"
                                        class="prod-thumb" alt="Product Item">
                                    <div>
                                        <strong><?php echo htmlspecialchars($p['productName']); ?></strong>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            <?php echo htmlspecialchars($p['productDescription']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span
                                    style="color:var(--primary-gold)"><?php echo htmlspecialchars($p['CategoryName'] ?? 'General'); ?></span>
                            </td>
                            <td>$<?php echo number_format($p['productPrice'], 2); ?></td>
                            <td>
                                <?php echo $p['productQuantity']; ?>
                                <?php if ($p['productQuantity'] <= 0): ?>
                                    <span class="badge badge-out">Out of Stock</span>
                                <?php elseif ($p['productQuantity'] <= 5): ?>
                                    <span class="badge badge-low">Low Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="inline-form" style="margin-right: 5px;">
                                    <input type="hidden" name="action" value="update_product">
                                    <input type="hidden" name="productId" value="<?php echo $p['productId']; ?>">
                                    <input type="number" name="productQuantity" value="<?php echo $p['productQuantity']; ?>"
                                        style="width:50px; padding:3px; font-size:12px; background:#111;">
                                    <button type="submit" class="btn"
                                        style="padding: 4px 8px; font-size: 11px; background:#2980b9; color:#fff;">Update
                                        Stock</button>
                                </form>

                                <form method="POST" class="inline-form"
                                    onsubmit="return confirm('Are you absolutely certain you want to purge this asset?');">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="productId" value="<?php echo $p['productId']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="grid">
            <div class="table-section" style="margin-bottom:0;">
                <h2>📂 Operational Categories Hierarchy</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Cat ID</th>
                            <th>Category Classification</th>
                            <th>Description</th>
                            <th>System Purge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>#<?php echo $c['CategoryId']; ?></td>
                                <td style="font-weight: bold; color: var(--primary-gold);">
                                    <?php echo htmlspecialchars($c['CategoryName']); ?></td>
                                <td style="color: var(--text-muted); font-size: 12px;">
                                    <?php echo htmlspecialchars($c['CategoryDescription']); ?></td>
                                <td>
                                    <form method="POST"
                                        onsubmit="return confirm('Warning: Deleting this category will un-link all related products. Proceed?');">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="CategoryId" value="<?php echo $c['CategoryId']; ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-section" style="margin-bottom:0;">
                <h2>👥 System Registered Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Profile Identity</th>
                            <th>Permissions Matrix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>#<?php echo $u['userId']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($u['userName']); ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($u['email']); ?></div>
                                </td>
                                <td>
                                    <span
                                        style="font-weight:bold; color: <?php echo $u['role'] === 'admin' ? 'var(--primary-gold)' : '#fff'; ?>;">
                                        <?php echo $u['role'] === 'admin' ? '⚙️ System Admin' : '👤 Customer Account'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>