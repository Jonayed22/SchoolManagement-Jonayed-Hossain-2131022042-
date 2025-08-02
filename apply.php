<?php
require_once 'DbConnectionSingleton.php';


$db = DbConnectionSingleton::getInstance()->getConnection();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name']);
    $gender     = $_POST['gender'];
    $birth_date = $_POST['birth_date'];
    $phone      = trim($_POST['phone']);
    $address    = trim($_POST['address']);
    $email      = trim($_POST['email']);

    if (!$full_name || !$gender || !$birth_date || !$phone || !$address || !$email) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
    } else {
        $stmt = $db->prepare("INSERT INTO student_applications (full_name, gender, birth_date, phone, address, email, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssssss", $full_name, $gender, $birth_date, $phone, $address, $email);

        if ($stmt->execute()) {
            $message = "Application submitted successfully! We will contact you soon.";
        } else {
            $message = "Error submitting application. Please try again later.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Student Application Form</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f9;
        padding: 30px;
        max-width: 500px;
        margin: auto;
    }
    h2 {
        text-align: center;
        color: #2c3e50;
    }
    form {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    input[type=text], input[type=date], input[type=email], select, textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0 15px 0;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        resize: vertical;
    }
    button {
        background-color: #007bff;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 18px;
        width: 100%;
    }
    button:hover {
        background-color: #0056b3;
    }
    .message {
        text-align: center;
        font-weight: 600;
        color: green;
        margin-bottom: 20px;
    }
    .error {
        color: red;
    }
    .back-button {
        margin-top: 15px;
        background-color: #6c757d;
    }
    .back-button:hover {
        background-color: #5a6268;
    }
</style>
</head>
<body>

<h2>Student Application Form</h2>

<?php if ($message): ?>
    <div class="message <?= strpos($message, 'Error') === false ? '' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <label for="full_name">Full Name</label>
    <input type="text" name="full_name" id="full_name" required placeholder="Your full name" value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">

    <label for="email">Email</label>
    <input type="email" name="email" id="email" required placeholder="Your email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

    <label for="gender">Gender</label>
    <select name="gender" id="gender" required>
        <option value="">-- Select Gender --</option>
        <option value="Male" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
        <option value="Other" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
    </select>

    <label for="birth_date">Birth Date</label>
    <input type="date" name="birth_date" id="birth_date" required value="<?= isset($_POST['birth_date']) ? htmlspecialchars($_POST['birth_date']) : '' ?>">

    <label for="phone">Phone</label>
    <input type="text" name="phone" id="phone" required placeholder="Your phone number" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">

    <label for="address">Address</label>
    <textarea name="address" id="address" rows="4" required placeholder="Your address"><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>

    <button type="submit">Apply</button>
</form>

<form action="dashboard.php" method="get">
    <button type="submit" class="back-button">Back to Dashboard</button>
</form>

</body>
</html>
