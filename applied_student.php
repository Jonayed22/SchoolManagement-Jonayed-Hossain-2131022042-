<?php
session_start();
include 'connection.php';

// Show all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Approve application
    if (isset($_POST['approve_id'])) {
        $app_id = intval($_POST['approve_id']);

        try {
            $stmt = $conn->prepare("SELECT * FROM student_applications WHERE id = ? AND status = 'pending'");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("i", $app_id);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            $result = $stmt->get_result();
            $app = $result->fetch_assoc();
            $stmt->close();

            if (!$app) {
                throw new Exception("Application not found or already processed.");
            }

            $email = $app['email'];

            // Check duplicate student email
            $stmt_check = $conn->prepare("SELECT id FROM students WHERE email = ?");
            if (!$stmt_check) throw new Exception("Prepare failed: " . $conn->error);
            $stmt_check->bind_param("s", $email);
            if (!$stmt_check->execute()) throw new Exception("Execute failed: " . $stmt_check->error);
            $result_check = $stmt_check->get_result();
            $existing_student = $result_check->fetch_assoc();
            $stmt_check->close();

            if ($existing_student) {
                throw new Exception("Student with this email already approved.");
            }

            $username = strtolower(str_replace(' ', '', $app['full_name'])) . rand(100, 999);
            $password = '1234'; // In production use password_hash()
            $role = 'student';

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("ssss", $username, $email, $password, $role);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            $user_id = $stmt->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO students (user_id, full_name, email, gender, birth_date, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("issssss", $user_id, $app['full_name'], $email, $app['gender'], $app['birth_date'], $app['phone'], $app['address']);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            $stmt->close();

            $stmt = $conn->prepare("UPDATE student_applications SET status='approved' WHERE id=?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("i", $app_id);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            $stmt->close();

            $success = "Student approved successfully!";

        } catch (Exception $ex) {
            $error = "Error: " . $ex->getMessage();
        }
    }

    // Decline application
    if (isset($_POST['decline_id'])) {
        $app_id = intval($_POST['decline_id']);

        try {
            $stmt = $conn->prepare("UPDATE student_applications SET status='declined' WHERE id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("i", $app_id);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            $stmt->close();

            $success = "Application declined successfully.";

        } catch (Exception $ex) {
            $error = "Error: " . $ex->getMessage();
        }
    }
}

$pending_apps = $conn->query("SELECT * FROM student_applications WHERE status = 'pending' ORDER BY id DESC");
if (!$pending_apps) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve/Decline Student Applications</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef2f5; padding: 30px; }
        h2 { text-align: center; margin-bottom: 10px; color: #2c3e50; }
        .message { text-align: center; padding: 12px; margin-bottom: 20px; font-weight: bold; border-radius: 6px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .application { background: #fff; border-radius: 10px; padding: 20px; margin: 20px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 800px; }
        .application p { margin: 5px 0; }
        .button-group { text-align: right; margin-top: 10px; }
        .button-group button { padding: 10px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin-left: 10px; font-weight: bold; }
        .approve-btn { background-color: #28a745; color: white; }
        .approve-btn:hover { background-color: #218838; }
        .decline-btn { background-color: #dc3545; color: white; }
        .decline-btn:hover { background-color: #c82333; }
        .no-applications { text-align: center; margin-top: 50px; font-size: 18px; color: #888; }
        .back-button { text-align: center; margin-top: 20px; }
        .back-button button { padding: 10px 20px; font-size: 16px; background-color: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .back-button button:hover { background-color: #5a6268; }
    </style>
</head>
<body>

<h2>Pending Student Applications</h2>

<div class="back-button">
    <form action="admin.php" method="get">
        <button type="submit">← Back to Admin Dashboard</button>
    </form>
</div>

<?php if ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php elseif ($error): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($pending_apps && $pending_apps->num_rows > 0): ?>
    <?php while ($app = $pending_apps->fetch_assoc()): ?>
        <div class="application">
            <p><strong>Name:</strong> <?= htmlspecialchars($app['full_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($app['email']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($app['gender']) ?></p>
            <p><strong>Birth Date:</strong> <?= htmlspecialchars($app['birth_date']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($app['phone']) ?></p>
            <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($app['address'])) ?></p>
            <div class="button-group">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="approve_id" value="<?= $app['id'] ?>">
                    <button class="approve-btn" onclick="return confirm('Approve this student?')">Approve</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="decline_id" value="<?= $app['id'] ?>">
                    <button class="decline-btn" onclick="return confirm('Decline this application?')">Decline</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="no-applications">No pending applications.</div>
<?php endif; ?>

</body>
</html>
