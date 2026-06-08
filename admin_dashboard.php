<?php
// admin_dashboard.php
session_start();
require_once 'db_connect.php';

// Security Check: Only allow logged-in Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>عذراً، غير مسموح لك بدخول هذه الصفحة. هذه اللوحة مخصصة للمشرفين فقط.</h2>");
}

$message = "";

// 1. Process Form Action Submissions (Implementing insertCategory and insertProduct)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Handle adding a new category
    if ($action === 'add_category') {
        $catName = trim($_POST['CategoryName'] ?? '');
        $catDesc = trim($_POST['CategoryDescription'] ?? '');

        if (!empty($catName)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO Category (CategoryName, CategoryDescription) VALUES (?, ?)");
                $stmt->execute([$catName, $catDesc]);
                $message = "تم إضافة الفئة بنجاح!";
            } catch (PDOException $e) {
                $message = "خطأ أثناء إضافة الفئة: " . $e->getMessage();
            }
        }
    }

    // Updated: Handle adding a new product WITH an actual image upload
    if ($action === 'add_product') {
        $pName = trim($_POST['productName'] ?? '');
        $pDesc = trim($_POST['productDescription'] ?? '');
        $pPrice = floatval($_POST['productPrice'] ?? 0);
        $pQty = intval($_POST['productQuantity'] ?? 0);
        $catId = intval($_POST['CategoryId'] ?? 0);

        $pImage = "placeholder.jpg"; // Default backup fallback

        // Process File Uploading
        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['productImage']['tmp_name'];
            $fileName = $_FILES['productImage']['name'];

            // Clean up the filename to avoid spaces/Arabic characters causing link breaks
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = time() . '_' . rand(1000, 9999) . '.' . $fileExtension;

            // Target destination path
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $pImage = $newFileName; // Save this newly generated unique filename string
            }
        }

        if (!empty($pName) && $pPrice > 0 && $catId > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO Product (productName, productDescription, productPrice, productQuantity, productImage, CategoryId) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$pName, $pDesc, $pPrice, $pQty, $pImage, $catId]);
                $message = "تم إضافة المنتج مع الصورة بنجاح إلى المخزن!";
            } catch (PDOException $e) {
                $message = "خطأ أثناء إضافة المنتج: " . $e->getMessage();
            }
        } else {
            $message = "الرجاء تعبئة الحقول الأساسية للمنتج بصورة صحيحة.";
        }
    }
}

// 2. Fetch inventory values to feed the control tables
try {
    $categories = $pdo->query("SELECT * FROM Category")->fetchAll();
    $products = $pdo->query("SELECT p.*, c.CategoryName FROM Product p LEFT JOIN Category c ON p.CategoryId = c.CategoryId")->fetchAll();
    $users = $pdo->query("SELECT userId, userName, email, role, phonenu FROM User")->fetchAll();
} catch (PDOException $e) {
    die("خطأ في الاتصال: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المشرف | Arabic Luxury Store</title>
    <style>
        :root {
            --primary-gold: #D4AF37;
            --dark-bg: #121212;
            --card-bg: #1E1E1E;
            --text-light: #F5F5F5;
            --text-muted: #A0A0A0;
            --emerald: #0A5C36;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #0A0A0A;
            border-bottom: 2px solid var(--primary-gold);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: var(--primary-gold);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .alert-msg {
            background-color: #2D2D2D;
            color: var(--primary-gold);
            padding: 15px;
            border-radius: 6px;
            border-right: 4px solid var(--primary-gold);
            margin-bottom: 20px;
            font-weight: bold;
        }

        /* Layout Admin Sections Grid */
        .admin-section {
            background-color: var(--card-bg);
            border: 1px solid #2A2A2A;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 40px;
        }

        h2,
        h3 {
            color: var(--primary-gold);
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        input,
        textarea,
        select {
            padding: 10px;
            background-color: #2D2D2D;
            border: 1px solid #444;
            border-radius: 4px;
            color: #fff;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-gold);
            outline: none;
        }

        .btn-submit {
            background-color: var(--primary-gold);
            color: var(--dark-bg);
            border: none;
            padding: 12px 24px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            width: fit-content;
            align-self: flex-end;
        }

        .btn-submit:hover {
            background-color: #e5be42;
        }

        /* Table Control Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            margin-top: 15px;
        }

        th {
            border-bottom: 2px solid var(--primary-gold);
            padding: 10px;
            color: var(--text-muted);
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #2A2A2A;
        }

        .badge {
            background-color: var(--emerald);
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <header>
        <span class="logo">👑 لوحة التحكم وإدارة النظام (Admin Panel)</span>
        <a href="index.php" style="color:#fff; text-decoration:none;">⬅ العودة للموقع الرئيسي</a>
    </header>

    <div class="dashboard-container">

        <?php if (!empty($message)): ?>
            <div class="alert-msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="admin-section">
            <h2>📁 إدارة الفئات (Manage Categories)</h2>
            <form method="POST" style="margin-bottom: 25px;">
                <input type="hidden" name="action" value="add_category">
                <div class="form-grid">
                    <div class="form-group">
                        <label>اسم الفئة الجديد *</label>
                        <input type="text" name="CategoryName" required placeholder="مثال: عطور عود">
                    </div>
                    <div class="form-group">
                        <label>وصف الفئة</label>
                        <input type="text" name="CategoryDescription" placeholder="وصف مختصر...">
                    </div>
                    <button type="submit" class="btn-submit">حفظ الفئة</button>
                </div>
            </form>

            <h3>الفئات الحالية</h3>
            <table>
                <thead>
                    <tr>
                        <th>معرف الفئة (ID)</th>
                        <th>اسم الفئة</th>
                        <th>الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>#<?php echo $cat['CategoryId']; ?></td>
                            <td><strong><?php echo htmlspecialchars($cat['CategoryName']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cat['CategoryDescription'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-section">
            <h2>📦 إدارة المنتجات والمخزن (Manage Products)</h2>
            <form method="POST" enctype="multipart/form-data" style="margin-bottom: 25px;">
                <input type="hidden" name="action" value="add_product">
                <div class="form-grid">
                    <div class="form-group">
                        <label>اسم المنتج *</label>
                        <input type="text" name="productName" required>
                    </div>
                    <div class="form-group">
                        <label>الفئة التابع لها *</label>
                        <select name="CategoryId" required>
                            <option value="">-- اختر فئة --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['CategoryId']; ?>">
                                    <?php echo htmlspecialchars($cat['CategoryName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>السعر الأساسي *</label>
                        <input type="number" step="0.01" name="productPrice" required placeholder="0.00 ر.س">
                    </div>
                    <div class="form-group">
                        <label>الكمية بالمخزن (Stock) *</label>
                        <input type="number" name="productQuantity" required value="10">
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: 2fr 1fr; margin-bottom:15px;">
                    <div class="form-group">
                        <label>وصف تفصيلي للمنتج</label>
                        <textarea name="productDescription" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>صورة المنتج *</label>
                        <input type="file" name="productImage" accept="image/*" style="padding:7px;" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">إضافة المنتج إلى الكتالوج</button>
            </form>

            <h3>المنتجات المتوفرة حالياً بالمخازن</h3>
            <table>
                <thead>
                    <tr>
                        <th>معرف المنتج</th>
                        <th>المنتج</th>
                        <th>الفئة</th>
                        <th>السعر</th>
                        <th>الكمية المتاحة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td>#<?php echo $prod['productId']; ?></td>
                            <td><strong><?php echo htmlspecialchars($prod['productName']); ?></strong></td>
                            <td><span
                                    class="badge"><?php echo htmlspecialchars($prod['CategoryName'] ?? 'غير مصنف'); ?></span>
                            </td>
                            <td><?php echo number_format($prod['productPrice'], 2); ?> ر.س</td>
                            <td>
                                <strong style="color: <?php echo $prod['productQuantity'] < 5 ? '#FF4D4D' : '#4BB543'; ?>">
                                    <?php echo $prod['productQuantity']; ?> وحدات
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-section">
            <h2>👥 إدارة مستخدمين النظام (Manage Users)</h2>
            <table>
                <thead>
                    <tr>
                        <th>رقم المستخدم</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>نوع الحساب (Role)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?php echo $u['userId']; ?></td>
                            <td><?php echo htmlspecialchars($u['userName']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span
                                    style="font-weight:bold; color: <?php echo $u['role'] === 'admin' ? 'var(--primary-gold)' : '#fff'; ?>">
                                    <?php echo $u['role'] === 'admin' ? '⚙️ مشرف النظام' : '👤 عميل'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>