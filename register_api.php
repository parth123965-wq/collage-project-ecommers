<?php
    // register_api.php
    header('Content-Type: application/json');
    require_once 'db_connect.php';

    // Check if data is coming through a POST request
    if ($_SERVER['REQUEST_URI'] ?? $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get raw JSON data from frontend JavaScript
        $input = json_decode(file_get_contents('php://input'), true);

        $userName = trim($input['userName'] ?? '');
        $email    = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        $address  = trim($input['address'] ?? '');
        $phonenu  = trim($input['phonenu'] ?? '');
        $dob      = trim($input['dob'] ?? '');

        // Basic Validation
        if (empty($userName) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }

        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT email FROM User WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
                exit;
            }

            // Securely hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user matching your 7 columns
            $sql = "INSERT INTO User (userName, email, password, address, phonenu, dob) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userName, $email, $hashedPassword, $address, $phonenu, $dob]);

            echo json_encode(['success' => true, 'message' => 'Registration successful! Please login.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Request Method.']);
    }
?>