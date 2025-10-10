<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';

// Handle withdrawal actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_id'], $_POST['action'])) {
    $withdrawal_id = intval($_POST['withdrawal_id']);
    $action = $_POST['action'];
    try {
        $pdo->beginTransaction();
        
        // Fetch the withdrawal request
        $stmt = $pdo->prepare("SELECT user_id, amount, status FROM withdrawals WHERE id = ? AND status = 'pending'");
        $stmt->execute([$withdrawal_id]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($withdrawal) {
            if ($action === 'approve') {
                // Update withdrawal status
                $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'success' WHERE id = ?");
                $stmt->execute([$withdrawal_id]);
                $_SESSION['success'] = "Withdrawal request approved successfully.";
            } elseif ($action === 'reject') {
                // Update withdrawal status and refund the amount
                $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'failed' WHERE id = ?");
                $stmt->execute([$withdrawal_id]);
                // Refund the amount to the user's balance
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$withdrawal['amount'], $withdrawal['user_id']]);
                $_SESSION['success'] = "Withdrawal request rejected and amount refunded.";
            }
            $pdo->commit();
        } else {
            $_SESSION['error'] = "Invalid or already processed withdrawal request.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Withdrawal action error: ' . $e->getMessage(), 3, '../debug.log');
        $_SESSION['error'] = "Failed to process withdrawal request: " . $e->getMessage();
    }
    header("Location: manage_withdrawals.php");
    exit;
}

// Fetch all withdrawal requests
try {
    $stmt = $pdo->prepare("
        SELECT w.id, w.user_id, w.amount, w.wallet_address, w.ref_number, w.status, w.created_at, u.email
        FROM withdrawals w
        JOIN users u ON w.user_id = u.id
        ORDER BY w.created_at DESC
    ");
    $stmt->execute();
    $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Withdrawal requests fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $withdrawals = [];
    $error = 'Failed to load withdrawal requests: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Manage Withdrawals</title>
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

        .withdrawals-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .withdrawals-table th,
        .withdrawals-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        .withdrawals-table th {
            background-color: #f8f9fa;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .withdrawals-table td {
            color: #555;
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

            .withdrawals-table {
                min-width: 100%;
            }

            .action-btn, .back-link {
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Manage Withdrawals</h2>
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

        <!-- Withdrawal Requests List -->
        <?php if (empty($withdrawals)): ?>
            <p>No withdrawal requests available.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="withdrawals-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Email</th>
                            <th>Amount ($)</th>
                            <th>Wallet Address</th>
                            <th>Ref Number</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $withdrawal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($withdrawal['id']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['email']); ?></td>
                                <td><?php echo number_format($withdrawal['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['wallet_address']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['ref_number']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($withdrawal['status'])); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['created_at']); ?></td>
                                <td>
                                    <?php if ($withdrawal['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="action-btn approve">Approve</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
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
