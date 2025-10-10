<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';

// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];
    try {
        $pdo->beginTransaction();
        
        // Fetch the verification request
        $stmt = $pdo->prepare("SELECT user_id FROM verification_requests WHERE id = ? AND status = 'pending'");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($request) {
            $user_id = $request['user_id'];
            if ($action === 'approve') {
                // Update verification request status
                $stmt = $pdo->prepare("UPDATE verification_requests SET status = 'approved' WHERE id = ?");
                $stmt->execute([$request_id]);
                // Update user verification status
                $stmt = $pdo->prepare("UPDATE users SET verification_status = 'verified' WHERE id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success'] = "Verification request approved successfully.";
            } elseif ($action === 'reject') {
                // Update verification request status
                $stmt = $pdo->prepare("UPDATE verification_requests SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$request_id]);
                // Update user verification status
                $stmt = $pdo->prepare("UPDATE users SET verification_status = 'not_verified' WHERE id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success'] = "Verification request rejected successfully.";
            }
            $pdo->commit();
        } else {
            $_SESSION['error'] = "Invalid or already processed verification request.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Verification action error: ' . $e->getMessage(), 3, '../debug.log');
        $_SESSION['error'] = "Failed to process verification request: " . $e->getMessage();
    }
    header("Location: manage_verifications.php");
    exit;
}

// Fetch all verification requests
try {
    $stmt = $pdo->prepare("
        SELECT vr.id, vr.user_id, vr.id_card_path, vr.selfie_path, vr.status, vr.submitted_at, u.email
        FROM verification_requests vr
        JOIN users u ON vr.user_id = u.id
        ORDER BY vr.submitted_at DESC
    ");
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Verification requests fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $requests = [];
    $error = 'Failed to load verification requests: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Manage Verification Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .dashboard-container h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .dashboard-container p {
            color: #555;
            font-size: 16px;
        }

        .back-link, .action-btn {
            display: inline-block;
            margin: 10px 5px;
            padding: 10px 20px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .back-link {
            background-color: #6c757d;
        }

        .back-link:hover {
            background-color: #5a6268;
        }

        .action-btn.approve {
            background-color: #28a745;
        }

        .action-btn.approve:hover {
            background-color: #218838;
        }

        .action-btn.reject {
            background-color: #dc3545;
        }

        .action-btn.reject:hover {
            background-color: #c82333;
        }

        .table-container {
            max-width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: 400px;
            margin-top: 10px;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .requests-table th,
        .requests-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        .requests-table th {
            background-color: #f8f9fa;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .requests-table td {
            color: #555;
        }

        .requests-table img {
            max-width: 100px;
            height: auto;
            border-radius: 4px;
        }

        .error, .success {
            color: red;
            margin-bottom: 15px;
        }

        .success {
            color: green;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                margin: 20px;
                padding: 15px;
            }

            .requests-table {
                min-width: 100%;
            }

            .action-btn, .back-link {
                width: 100%;
                margin: 10px 0;
            }

            .requests-table img {
                max-width: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Manage Verification Requests</h2>
        <p>Hello, <?php echo htmlspecialchars($_SESSION['admin_email']); ?>!</p>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <a href="admin.php" class="back-link">Back to Dashboard</a>

        <!-- Verification Requests List -->
        <?php if (empty($requests)): ?>
            <p>No verification requests available.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Email</th>
                            <th>ID Card</th>
                            <th>Selfie</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['id']); ?></td>
                                <td><?php echo htmlspecialchars($request['email']); ?></td>
                                <td><img src="../<?php echo htmlspecialchars($request['id_card_path']); ?>" alt="ID Card"></td>
                                <td><img src="../<?php echo htmlspecialchars($request['selfie_path']); ?>" alt="Selfie"></td>
                                <td><?php echo htmlspecialchars(ucfirst($request['status'])); ?></td>
                                <td><?php echo htmlspecialchars($request['submitted_at']); ?></td>
                                <td>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="action-btn approve">Approve</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="action-btn reject">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span>No actions available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
