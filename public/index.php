<?php
// index.php
session_start();
require_once '../config/db_connect.php';

try {
    $catQuery = $pdo->query("SELECT * FROM Category");
    $categories = $catQuery->fetchAll();

    $prodQuery = $pdo->query("SELECT p.*, c.CategoryName FROM Product p LEFT JOIN Category c ON p.CategoryId = c.CategoryId");
    $products = $prodQuery->fetchAll();
} catch (PDOException $e) {
    die("Data fetch error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arabic Luxury Store | Home</title>
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
            letter-spacing: 1px;
        }

        .nav-user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .welcome-text {
            color: var(--text-muted);
            font-size: 15px;
        }

        .welcome-text strong {
            color: var(--primary-gold);
        }

        .btn-cart {
            color: var(--primary-gold);
            text-decoration: none;
            font-weight: bold;
        }

        .btn-logout {
            color: #FF4D4D;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid #FF4D4D;
            padding: 6px 12px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background-color: #FF4D4D;
            color: #fff;
        }

        .btn-login {
            color: var(--primary-gold);
            text-decoration: none;
            border: 1px solid var(--primary-gold);
            padding: 6px 12px;
            border-radius: 4px;
        }

        .hero-banner {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(rgba(20, 20, 20, 0.8), rgba(24, 24, 24, 0.95)), url('https://images.unsplash.com/photo-1595425970377-c9703cf48b6d?q=80&w=1200') center/cover;
            border-bottom: 1px solid #2A2A2A;
        }

        .hero-banner h1 {
            color: var(--primary-gold);
            font-size: 42px;
            margin: 0 0 10px 0;
        }

        .hero-banner p {
            color: var(--text-muted);
            font-size: 18px;
            margin: 0;
        }

        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-title {
            color: var(--primary-gold);
            border-left: 4px solid var(--primary-gold);
            padding-left: 12px;
            margin-bottom: 30px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background-color: var(--card-bg);
            border: 1px solid #2A2A2A;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, border-color 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-gold);
        }

        .product-info {
            padding: 20px;
            flex-grow: 1;
        }

        .product-category {
            font-size: 12px;
            color: var(--primary-gold);
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .product-name {
            font-size: 18px;
            margin: 0 0 10px 0;
            color: var(--text-light);
        }

        .product-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 15px;
            height: 42px;
            overflow: hidden;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-gold);
        }

        .btn-add-cart {
            background-color: transparent;
            border: 1px solid var(--primary-gold);
            color: var(--primary-gold);
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            background-color: var(--primary-gold);
            color: var(--dark-bg);
        }
    </style>
</head>

<body>

    <header>
        <a href="../public/index.php" class="logo">ARABIC LUXURY Store</a>

        <div class="nav-user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="welcome-text">Welcome,
                    <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                <a href="../public/cart.php" class="btn-cart">🛒 Shopping Cart</a>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="../admin/admin_dashboard.php" style="color: cyan; text-decoration: none; font-size:14px;">Dashboard</a>
                <?php endif; ?>

                <a href="../api/logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="../auth/login.html" class="btn-login">Login / Sign In</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="hero-banner">
        <h1>Eastern Luxury Collection</h1>
        <p>Discover the finest authentic Arab fragrances and heritage collections</p>
    </div>

    <div class="main-container">
        <h2 class="section-title">Featured Products</h2>

        <div class="products-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div
                            style="width: 100%; height: 220px; background-color: #252525; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <?php if (!empty($product['productImage']) && file_exists('../uploads/' . $product['productImage'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($product['productImage']); ?>" alt="Product">
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 14px;">📦 No Image Available</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-info">
                            <span
                                class="product-category"><?php echo htmlspecialchars($product['CategoryName'] ?? 'General'); ?></span>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['productName']); ?></h3>
                            <p class="product-desc"><?php echo htmlspecialchars($product['productDescription']); ?></p>

                            <div class="product-meta">
                                <span class="product-price">$<?php echo number_format($product['productPrice'], 2); ?></span>
                                <button class="btn-add-cart" onclick="addToCart(<?php echo $product['productId']; ?>)">Add to
                                    Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        async function addToCart(productId) {
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

            if (!isLoggedIn) {
                alert('Please login first to add items to your cart!');
                window.location.href = 'login.html';
                return;
            }

            try {
                const response = await fetch('../api/add_to_cart_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ productId: productId, quantity: 1 })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('An unexpected error occurred while adding to cart.');
            }
        }
    </script>
</body>

</html>