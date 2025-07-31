<?php
session_start();
include 'connection.php';

// Count data from DB
$total_students = $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
$total_teachers = $conn->query("SELECT COUNT(*) AS count FROM teachers")->fetch_assoc()['count'];
$total_classes = $conn->query("SELECT COUNT(*) AS count FROM classes")->fetch_assoc()['count'];
$total_announcements = $conn->query("SELECT COUNT(*) AS count FROM announcements")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>School Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Orbitron', sans-serif;
    }

    body {
      background-color: #f4f6f9;
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header {
      background-color: #ffffff;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #ddd;
    }

    header h1 {
      font-size: 26px;
      color: #2c3e50;
    }

    nav a {
      text-decoration: none;
      color: #007bff;
      font-weight: 600;
      font-size: 16px;
      padding: 8px 15px;
      border: 1px solid #007bff;
      border-radius: 6px;
      transition: background-color 0.3s ease, color 0.3s ease;
      margin-left: 10px;
      display: inline-block;
    }

    nav a:first-child {
      margin-left: 0;
    }

    nav a:hover {
      background-color: #007bff;
      color: #fff;
    }

    .container {
      flex-grow: 1;
      padding: 40px 20px;
      max-width: 1200px;
      margin: auto;
    }

    .welcome-title {
      text-align: center;
      font-size: 32px;
      margin-bottom: 40px;
      color: #2c3e50;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 30px;
    }

    .card {
      background: #fff;
      padding: 30px 20px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      text-align: center;
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    .card h2 {
      font-size: 40px;
      color: #007bff;
      margin-bottom: 10px;
    }

    .card p {
      font-size: 18px;
      color: #444;
      font-weight: 500;
    }

    footer {
      text-align: center;
      background: #f8f9fa;
      padding: 15px;
      font-size: 14px;
      color: #666;
      border-top: 1px solid #ddd;
    }
  </style>
</head>
<body>

  <header>
    <h1>School Management System</h1>
    <nav>
      <a href="apply.php">Apply</a>
      
      <a href="login.php">Login</a>
    </nav>
  </header>

  <div class="container">
    <div class="welcome-title">Welcome to School Dashboard</div>

    <div class="cards">
      <div class="card">
        <h2><?= $total_students ?></h2>
        <p>Total Students</p>
      </div>
      <div class="card">
        <h2><?= $total_teachers ?></h2>
        <p>Total Teachers</p>
      </div>
      <div class="card">
        <h2><?= $total_classes ?></h2>
        <p>Total Classes</p>
      </div>
      <div class="card">
        <h2><?= $total_announcements ?></h2>
        <p>Total Announcements</p>
      </div>
    </div>
  </div>

  <footer>
    &copy; <?= date('Y') ?> School Management System. All rights reserved.
  </footer>

</body>
</html>
