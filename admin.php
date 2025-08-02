<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'connection.php';


class AdminDashboard {
    private $conn;
    public $data = [];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function loadData() {
        $this->data['total_students'] = $this->conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
        $this->data['total_teachers'] = $this->conn->query("SELECT COUNT(*) AS count FROM teachers")->fetch_assoc()['count'];
        $this->data['total_classes'] = $this->conn->query("SELECT COUNT(*) AS count FROM classes")->fetch_assoc()['count'];
        $this->data['total_announcements'] = $this->conn->query("SELECT COUNT(*) AS count FROM announcements")->fetch_assoc()['count'];
    }

    public function getData() {
        return $this->data;
    }
}

class DashboardFactory {
    public static function createDashboard($role, $conn) {
    
        if ($role === 'admin') {
            $dashboard = new AdminDashboard($conn);
            $dashboard->loadData();
            return $dashboard;
        } else {
            header('Location: login.php');
            exit();
        }
    }
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$dashboard = DashboardFactory::createDashboard($_SESSION['role'], $conn);
$data = $dashboard->getData();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        :root {
            --primary: #1f2937;
            --accent: #ef4444;
            --background: #f9fafb;
            --card-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --shadow: rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text-main);
            padding-top: 70px;
            min-height: 100vh;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px;
            background-color: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            box-shadow: 0 2px 8px var(--shadow);
            z-index: 1000;
        }

        header h1 {
            font-size: 24px;
            font-weight: 700;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 700;
            background: var(--accent);
            padding: 10px 16px;
            border-radius: 8px;
            transition: var(--transition);
        }

        nav a:hover {
            background: #dc2626;
        }

        main {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 6px 18px var(--shadow);
            transition: var(--transition);
            text-decoration: none;
            color: inherit;
            border: 1px solid #e5e7eb;
            user-select: none;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 30px rgba(239, 68, 68, 0.3);
            border-color: var(--accent);
            cursor: pointer;
        }

        .card h2 {
            font-size: 40px;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        .card p {
            font-size: 18px;
            font-weight: 500;
            color: var(--text-muted);
        }

        footer {
            margin-top: 40px;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
        }

        @media (max-width: 600px) {
            header h1 {
                font-size: 18px;
            }

            nav a {
                font-size: 14px;
                padding: 8px 14px;
            }

            .card h2 {
                font-size: 30px;
            }

            .card p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <nav><a href="logout.php">Logout</a></nav>
</header>

<main>
    <a href="manage_students.php" class="card">
        <h2><?= htmlspecialchars($data['total_students']) ?></h2>
        <p>Total Students</p>
    </a>

    <a href="applied_student.php" class="card">
        <h2>+</h2>
        <p>Approve Students</p>
    </a>

    <a href="manage_teachers.php" class="card">
        <h2><?= htmlspecialchars($data['total_teachers']) ?></h2>
        <p>Total Teachers</p>
    </a>

    <a href="manage_classes.php" class="card">
        <h2><?= htmlspecialchars($data['total_classes']) ?></h2>
        <p>Total Classes</p>
    </a>

    <a href="manage_announcements.php" class="card">
        <h2><?= htmlspecialchars($data['total_announcements']) ?></h2>
        <p>Announcements</p>
    </a>

    <a href="s_assigned.php" class="card">
        <h2>📚</h2>
        <p>Assign Class</p>
    </a>

    <a href="exam.php" class="card">
        <h2>📝</h2>
        <p>Manage Exams</p>
    </a>

    <a href="result.php" class="card">
        <h2>🎓</h2>
        <p>Manage Results</p>
    </a>
</main>

<footer>
    &copy; <?= date('Y') ?> School Management System. All rights reserved.
</footer>

</body>
</html>
