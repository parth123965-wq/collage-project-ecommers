<?php
    // add_to_cart_api.php
    session_start();
    header('Content-Type: application/json');
    require_once 'db_connect.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'الرجاء تسجيل الدخول أولاً.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = intval($input['productId'] ?? 0);
        $quantity  = intval($input['quantity'] ?? 1);

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صالح.']);
            exit;
        }

        try {
            // 1. Fetch Product details along with its Category Name (matching DFD/Class requirements)
            $pStmt = $pdo->prepare("SELECT p.*, c.CategoryName FROM Product p LEFT JOIN Category c ON p.CategoryId = c.CategoryId WHERE p.productId = ?");
            $pStmt->execute([$productId]);
            $product = $pStmt->fetch();

            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'المنتج غير موجود.']);
                exit;
            }

            // 2. Fetch User Profile Details to populate the custom Cart layout snapshot
            $uStmt = $pdo->prepare("SELECT * FROM User WHERE userId = ?");
            $uStmt->execute([$_SESSION['user_id']]);
            $user = $uStmt->fetch();

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود.']);
                exit;
            }

            // 3. Perform Tax Calculation (Simulating an standard 15% VAT calculation)
            $taxRate = 0.15; 
            $priceWithTax = $product['productPrice'] * (1 + $taxRate);

            // 4. Run the insertcart() statement incorporating all 13 mapped layout columns
            $sql = "INSERT INTO Cart (
                        p_id, p_name, p_des, p_price, p_pricewithtax, p_image, p_qty, CategoryName,
                        u_id, u_name, u_address, u_phonenu, u_email
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $product['productId'],
                $product['productName'],
                $product['productDescription'],
                $product['productPrice'],
                $priceWithTax,
                $product['productImage'],
                $quantity,
                $product['CategoryName'] ?? 'General',
                $user['userId'],
                $user['userName'],
                $user['address'],
                $user['phonenu'],
                $user['email']
            ]);

            echo json_encode(['success' => true, 'message' => 'تم إضافة المنتج إلى سلة المشتريات الفاخرة بنجاح!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'إجراء غير مسموح به.']);
    }
?>