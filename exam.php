<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}



interface ExamCreator {
    public function create($name, $class_id, $section_id, $exam_date, $total_marks);
}

class BaseExamCreator implements ExamCreator {
    protected $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($name, $class_id, $section_id, $exam_date, $total_marks) {
        $stmt = $this->conn->prepare(
            "INSERT INTO exams (name, class_id, section_id, exam_date, total_marks) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("siisi", $name, $class_id, $section_id, $exam_date, $total_marks);
        $stmt->execute();
        $stmt->close();
    }
}

abstract class ExamDecorator implements ExamCreator {
    protected $examCreator;

    public function __construct(ExamCreator $examCreator) {
        $this->examCreator = $examCreator;
    }

    public function create($name, $class_id, $section_id, $exam_date, $total_marks) {
        $this->examCreator->create($name, $class_id, $section_id, $exam_date, $total_marks);
    }
}

class LoggingExamDecorator extends ExamDecorator {
    public function create($name, $class_id, $section_id, $exam_date, $total_marks) {
        parent::create($name, $class_id, $section_id, $exam_date, $total_marks);
        $log = "[" . date('Y-m-d H:i:s') . "] Exam created: $name | Class ID: $class_id | Section ID: $section_id\n";
        file_put_contents('exam_log.txt', $log, FILE_APPEND);
    }
}


$classes = $conn->query("SELECT * FROM classes");
$sections = $conn->query("SELECT * FROM sections");

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $class_id = $_POST['class_id'];
    $section_id = $_POST['section_id'];
    $exam_date = $_POST['exam_date'];
    $total_marks = $_POST['total_marks'];


    $creator = new LoggingExamDecorator(new BaseExamCreator($conn));
    $creator->create($name, $class_id, $section_id, $exam_date, $total_marks);

    $success = "✅ Exam added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Exams</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --bg: #f0f4f8;
            --white: #ffffff;
            --primary: #3b82f6;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --radius: 10px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --success: #10b981;
            --success-bg: #d1fae5;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }

        .back-button {
            max-width: 600px;
            margin: 30px auto 0;
            text-align: left;
        }

        .back-button a {
            display: inline-block;
            background-color: #e5e7eb;
            color: var(--text-dark);
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: background 0.2s ease;
        }

        .back-button a:hover {
            background-color: #d1d5db;
        }

        .container {
            max-width: 600px;
            margin: 20px auto 50px;
            padding: 30px;
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        h2 {
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
            border-radius: var(--radius);
            background-color: #f9fafb;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #2563eb;
        }

        .success {
            background-color: var(--success-bg);
            color: var(--success);
            border: 1px solid var(--success);
            padding: 10px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 20px;
            }

            h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<div class="back-button">
    <a href="admin.php">← Back to Dashboard</a>
</div>

<div class="container">
    <h2>📅 Create New Exam</h2>

    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Exam Name</label>
        <input type="text" name="name" id="name" placeholder="e.g. Midterm Exam" required>

        <label for="class_id">Class</label>
        <select name="class_id" id="class_id" required>
            <option value="">Select Class</option>
            <?php while($row = $classes->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="section_id">Section</label>
        <select name="section_id" id="section_id" required>
            <option value="">Select Section</option>
            <?php while($row = $sections->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="exam_date">Exam Date</label>
        <input type="date" name="exam_date" id="exam_date" required>

        <label for="total_marks">Total Marks</label>
        <input type="number" name="total_marks" id="total_marks" placeholder="e.g. 100" required>

        <button type="submit">➕ Add Exam</button>
    </form>
</div>

</body>
</html>
