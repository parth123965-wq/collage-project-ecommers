<?php
    // login_api.php
    session_start();
    header('Content-Type: application/json');
    require_once 'db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $email    = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'الرجاء إدخال البريد الإلكتروني وكلمة المرور.']); // Please enter email and password
            exit;
        }

        try {
            // Fetch user from database
            $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Check if user exists and verify password
            if ($user && password_verify($password, $user['password'])) {
                // Save user details to Session matching your class properties
                $_SESSION['user_id']   = $user['userId'];
                $_SESSION['user_name'] = $user['userName'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['address']   = $user['address'];
                $_SESSION['phonenu']   = $user['phonenu'];

                echo json_encode([
                    'success' => true, 
                    'message' => 'تم تسجيل الدخول بنجاح!', // Login successful!
                    'userName' => $user['userName']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.']); // Incorrect credentials
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'طلب غير صالح.']);
    }
?>