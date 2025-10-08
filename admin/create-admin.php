<?php
// admin/create-admin.php
session_start();
require_once '../database/conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch current admin's name for display
try {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = htmlspecialchars($user['name']);
} catch (PDOException $e) {
    error_log('Fetch user error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: login.php?error=database');
    exit;
}

// Handle form submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $passcode = $_POST['passcode'];

    // Server-side validation
    if (empty($name) || empty($email) || empty($passcode)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($passcode) < 8) {
        $error = 'Passcode must be at least 8 characters long.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already exists.';
            } else {
                // Hash passcode and insert new admin
                $hashedPasscode = password_hash($passcode, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, passcode, role, balance) VALUES (?, ?, ?, 'admin', 0.00)");
                $stmt->execute([$name, $email, $hashedPasscode]);
                $success = 'Admin user created successfully!';
            }
        } catch (PDOException $e) {
            error_log('Create admin error: ' . $e->getMessage(), 3, '../debug.log');
            $error = 'An error occurred while creating the admin. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create a new admin user for Task Tube to manage platform settings and video uploads.">
    <meta name="keywords" content="Task Tube, admin, create admin, manage users">
    <meta name="author" content="Task Tube">
    <title>Create Admin | Task Tube</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #f7f9fc;
            --card-bg: #ffffff;
            --text-color: #1a1a1a;
            --subtext-color: #6b7280;
            --border-color: #d1d5db;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --accent-color: #22c55e;
            --accent-hover: #16a34a;
        }

        [data-theme="dark"] {
            --bg-color: #1f2937;
            --card-bg: #2d3748;
            --text-color: #e5e7eb;
            --subtext-color: #9ca3af;
            --border-color: #4b5563;
            --shadow-color: rgba(0, 0, 0, 0.3);
            --accent-color: #34d399;
            --accent-hover: #22c55e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 400px;
            width: 100%;
            padding: 24px;
        }

        .form-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 6px 16px var(--shadow-color);
            text-align: center;
        }

        .form-card h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .input-container {
            position: relative;
            margin-bottom: 28px;
        }

        .input-container input {
            width: 100%;
            padding: 14px 0;
            font-size: 16px;
            border: none;
            border-bottom: 2px solid var(--border-color);
            background: transparent;
            color: var(--text-color);
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-container input:focus,
        .input-container input:valid {
            border-bottom-color: var(--accent-color);
        }

        .input-container label {
            position: absolute;
            top: 14px;
            left: 0;
            font-size: 16px;
            color: var(--subtext-color);
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-container input:focus ~ label,
        .input-container input:valid ~ label {
            top: -18px;
            font-size: 12px;
            color: var(--accent-color);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--accent-color);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .submit-btn:hover {
            background: var(--accent-hover);
            transform: scale(1.02);
        }

        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            color: var(--accent-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .back-btn:hover {
            color: var(--accent-hover);
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <h1>Create New Admin | Task Tube</h1>
            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php elseif ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" id="createAdminForm">
                <div class="input-container">
                    <input type="text" id="name" name="name" required>
                    <label for="name">Full Name</label>
                </div>
                <div class="input-container">
                    <input type="email" id="email" name="email" required>
                    <label for="email">Email Address</label>
                </div>
                <div class="input-container">
                    <input type="password" id="passcode" name="passcode" required>
                    <label for="passcode">Passcode</label>
                </div>
                <button type="submit" class="submit-btn">Create Admin</button>
            </form>
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>

    <script>
        // Client-side form validation
        document.getElementById('createAdminForm').addEventListener('submit', function(event) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const passcode = document.getElementById('passcode').value;

            if (!name || !email || !passcode) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Fields',
                    text: 'Please fill in all fields.'
                });
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address.'
                });
            } else if (passcode.length < 8) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Passcode',
                    text: 'Passcode must be at least 8 characters long.'
                });
            }
        });

        // Display success message and redirect
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo htmlspecialchars($success); ?>',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        <?php endif; ?>
    </script>
</body>
</html>
