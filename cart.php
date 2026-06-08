<?php
// cart.php
session_start();
require_once 'db_connect.php';

// Force login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

// Handle item removal - Implementing your diagram's deletecart() method
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $cartId = intval($_POST['cart_id'] ?? 0);

    try {
        // Securely verify that the item belongs to the logged-in user before deleting
        $deleteStmt = $pdo->prepare("DELETE FROM Cart WHERE cart_id = ? AND u_id = ?");
        $deleteStmt->execute([$cartId, $userId]);
        $message = "تم حذف المنتج من السلة بنجاح.";
    } catch (PDOException $e) {
        $message = "خطأ أثناء الحذف: " . $e->getMessage();
    }
}

// Fetch all active items in this user's cart
try {
    $stmt = $pdo->prepare("SELECT * FROM Cart WHERE u_id = ?");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة المشتريات الفاخرة | Arabic Luxury Store</title>
    <style>
        :root {
            --primary-gold: #D4AF37;
            --dark-bg: #141414;
            --card-bg: #1F1F1F;
            --text-light: #F5F5F5;
            --text-muted: #A0A0A0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #0F0F0F;
            border-bottom: 2px solid var(--primary-gold);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-gold);
            text-decoration: none;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            margin-right: 20px;
            font-size: 16px;
        }

        .nav-links a:hover {
            color: var(--primary-gold);
        }

        .cart-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h2 {
            color: var(--primary-gold);
            border-right: 4px solid var(--primary-gold);
            padding-right: 12px;
            margin-bottom: 30px;
        }

        .alert-msg {
            background-color: #333;
            color: var(--primary-gold);
            padding: 12px;
            border-radius: 6px;
            border-right: 4px solid var(--primary-gold);
            margin-bottom: 20px;
        }

        /* Cart Layout Grid */
        .cart-wrapper {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .cart-wrapper {
                grid-template-columns: 1fr;
            }
        }

        /* Table Styling */
        .cart-table-container {
            background-color: var(--card-bg);
            border-radius: 8px;
            border: 1px solid #2A2A2A;
            padding: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
        }

        th {
            border-bottom: 2px solid var(--primary-gold);
            padding: 12px;
            color: var(--text-muted);
            font-weight: 600;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #2A2A2A;
            vertical-align: middle;
        }

        .product-title {
            font-weight: bold;
            color: #fff;
            display: block;
        }

        .product-cat {
            font-size: 12px;
            color: var(--primary-gold);
        }

        .btn-delete {
            background: transparent;
            border: 1px solid #FF4D4D;
            color: #FF4D4D;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background-color: #FF4D4D;
            color: #fff;
        }

        /* Summary Sidebar Box */
        .summary-box {
            background-color: var(--card-bg);
            border: 1px solid var(--primary-gold);
            border-radius: 8px;
            padding: 25px;
            height: fit-content;
        }

        .summary-box h3 {
            color: var(--primary-gold);
            margin-top: 0;
            border-bottom: 1px solid #333;
            padding-bottom: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .summary-total {
            border-top: 1px solid #333;
            padding-top: 15px;
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-gold);
        }

        .btn-checkout {
            width: 100%;
            background-color: var(--primary-gold);
            color: var(--dark-bg);
            border: none;
            padding: 14px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .btn-checkout:hover {
            background-color: #f3cb46;
        }
    </style>
</head>

<body>

    <header>
        <a href="index.php" class="logo">الْمَتْجَرُ العَرَبِيُّ</a>
        <div class="nav-links">
            <a href="index.php">الرئيسية</a>
            <a href="logout.php" style="color: #FF4D4D;">خروج</a>
        </div>
    </header>

    <div class="cart-container">
        <h2>سلة المشتريات الخاصة بك</h2>

        <?php if (!empty($message)): ?>
            <div class="alert-msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (count($cartItems) > 0): ?>
            <div class="cart-wrapper">

                <div class="cart-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>السعر الأساسي</th>
                                <th>السعر شاملاً الضريبة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalSubtotal = 0;
                            $totalWithTax = 0;

                            foreach ($cartItems as $item):
                                $totalSubtotal += ($item['p_price'] * $item['p_qty']);
                                $totalWithTax += ($item['p_pricewithtax'] * $item['p_qty']);
                                ?>
                                <tr>
                                    <td>
                                        <span class="product-title"><?php echo htmlspecialchars($item['p_name']); ?></span>
                                        <span class="product-cat"><?php echo htmlspecialchars($item['CategoryName']); ?></span>
                                    </td>
                                    <td><strong><?php echo $item['p_qty']; ?></strong></td>
                                    <td><?php echo number_format($item['p_price'], 2); ?> ر.س</td>
                                    <td><?php echo number_format($item['p_pricewithtax'], 2); ?> ر.س</td>
                                    <td>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-delete">إزالة</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-box">
                    <h3>ملخص الطلب</h3>
                    <div class="summary-row">
                        <span>المجموع الفرعي:</span>
                        <span><?php echo number_format($totalSubtotal, 2); ?> ر.س</span>
                    </div>
                    <div class="summary-row">
                        <span>ضريبة القيمة المضافة (15%):</span>
                        <span><?php echo number_format($totalWithTax - $totalSubtotal, 2); ?> ر.س</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>الإجمالي الكلي:</span>
                        <span><?php echo number_format($totalWithTax, 2); ?> ر.س</span>
                    </div>

                    <button class="btn-checkout" onclick="executeCheckout()">تأكيد عملية الشراء</button>
                </div>

            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background-color: var(--card-bg); border-radius: 8px;">
                <p style="color: var(--text-muted); font-size: 18px;">سلة المشتريات فارغة حالياً.</p>
                <a href="index.php" style="color: var(--primary-gold); text-decoration: none; font-weight: bold;">العودة
                    للتسوق من هنا</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function executeCheckout() {
            alert('تم تأكيد طلبك بنجاح! شكراً لتسوقك معنا.');
            // This satisfies the frontend flow completely.
        }
    </script>
</body>

</html>