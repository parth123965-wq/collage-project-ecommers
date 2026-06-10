<?php
// cart.php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.html");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $cartId = intval($_POST['cart_id'] ?? 0);

    try {
        $deleteStmt = $pdo->prepare("DELETE FROM Cart WHERE cart_id = ? AND u_id = ?");
        $deleteStmt->execute([$cartId, $userId]);
        $message = "Item removed from cart successfully.";
    } catch (PDOException $e) {
        $message = "Error during deletion: " . $e->getMessage();
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Cart WHERE u_id = ?");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Arabic Luxury Store</title>
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
            margin-left: 20px;
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
            border-left: 4px solid var(--primary-gold);
            padding-left: 12px;
            margin-bottom: 30px;
        }

        .alert-msg {
            background-color: #333;
            color: var(--primary-gold);
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid var(--primary-gold);
            margin-bottom: 20px;
        }

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
            text-align: left;
        }

        th {
            border-bottom: 2px solid var(--primary-gold);
            padding: 12px;
            color: var(--text-muted);
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #2A2A2A;
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
        <a href="../public/index.php" class="logo">ARABIC LUXURY</a>
        <div class="nav-links">
            <a href="../public/index.php">Home Shop</a>
            <a href="../api/logout.php" style="color: #FF4D4D;">Logout</a>
        </div>
    </header>

    <div class="cart-container">
        <h2>Your Shopping Cart</h2>

        <?php if (!empty($message)): ?>
            <div class="alert-msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (count($cartItems) > 0): ?>
            <div class="cart-wrapper">

                <div class="cart-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Price (+ Tax)</th>
                                <th>Action</th>
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
                                    <td>$<?php echo number_format($item['p_price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['p_pricewithtax'], 2); ?></td>
                                    <td>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-delete">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-box">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($totalSubtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>VAT (15%):</span>
                        <span>$<?php echo number_format($totalWithTax - $totalSubtotal, 2); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total Amount:</span>
                        <span>$<?php echo number_format($totalWithTax, 2); ?></span>
                    </div>

                    <button class="btn-checkout" onclick="executeCheckout()">Proceed to Checkout</button>
                </div>

            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background-color: var(--card-bg); border-radius: 8px;">
                <p style="color: var(--text-muted); font-size: 18px;">Your shopping cart is currently empty.</p>
                <a href="../public/index.php" style="color: var(--primary-gold); text-decoration: none; font-weight: bold;">Return to
                    Store</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function executeCheckout() {
            alert('Your order has been verified and placed successfully! Thank you for shopping with us.');
        }
    </script>
</body>

</html>