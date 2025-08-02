<?php
session_start();
include 'connection.php';

interface Observer {
    public function update($event, $data);
}

class Logger implements Observer {
    public function update($event, $data) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Event: $event";
        if (isset($data['title'])) {
            $logMessage .= " - Title: " . $data['title'];
        }
        $logMessage .= PHP_EOL;
        file_put_contents('announcement_log.txt', $logMessage, FILE_APPEND);
    }
}


class AnnouncementSubject {
    private $observers = [];
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }


    public function attach(Observer $observer) {
        $this->observers[] = $observer;
    }

    
    private function notify($event, $data) {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }


    public function addAnnouncement($title, $message, $created_by) {
        $stmt = $this->conn->prepare("INSERT INTO announcements (title, message, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $title, $message, $created_by);
        $stmt->execute();
        $stmt->close();

        $this->notify('Announcement Added', ['title' => $title, 'message' => $message]);
    }


    public function updateAnnouncement($id, $title, $message) {
        $stmt = $this->conn->prepare("UPDATE announcements SET title=?, message=? WHERE id=?");
        $stmt->bind_param('ssi', $title, $message, $id);
        $stmt->execute();
        $stmt->close();

        $this->notify('Announcement Updated', ['title' => $title, 'message' => $message]);
    }


    public function deleteAnnouncement($id) {
        
        $res = $this->conn->query("SELECT title FROM announcements WHERE id=$id");
        $row = $res->fetch_assoc();
        $title = $row['title'] ?? '';

        $stmt = $this->conn->prepare("DELETE FROM announcements WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $this->notify('Announcement Deleted', ['title' => $title, 'id' => $id]);
    }
}

// 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}


$announcementSubject = new AnnouncementSubject($conn);
$logger = new Logger();
$announcementSubject->attach($logger);


if (isset($_POST['add_announcement'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);
    $created_by = $_SESSION['user_id'];

    $announcementSubject->addAnnouncement($title, $message, $created_by);
    header("Location: manage_announcements.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $announcementSubject->deleteAnnouncement($id);
    header("Location: manage_announcements.php");
    exit();
}

$edit_announcement = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM announcements WHERE id=$id");
    $edit_announcement = $res->fetch_assoc();
}

if (isset($_POST['edit_announcement'])) {
    $id = (int)$_POST['id'];
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);

    $announcementSubject->updateAnnouncement($id, $title, $message);
    header("Location: manage_announcements.php");
    exit();
}

$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Announcements - Admin</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
h1 { color: #333; }
form { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; max-width: 600px; }
input[type=text], textarea { width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: 1px solid #ccc; }
textarea { resize: vertical; height: 100px; }
button { padding: 10px 15px; background: #764ba2; color: white; border: none; cursor: pointer; border-radius: 6px; }
button:hover { background: #5b3b8b; }
table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; }
a { color: #764ba2; text-decoration: none; margin-right: 10px; }
a.delete { color: red; }
</style>
</head>
<body>

<a href="admin.php">← Back to Dashboard</a>

<h1><?= $edit_announcement ? "Edit Announcement" : "Add Announcement" ?></h1>

<form method="POST">
    <?php if ($edit_announcement): ?>
        <input type="hidden" name="id" value="<?= $edit_announcement['id'] ?>">
    <?php endif; ?>

    <label>Title</label>
    <input type="text" name="title" required value="<?= $edit_announcement ? htmlspecialchars($edit_announcement['title']) : '' ?>">

    <label>Message</label>
    <textarea name="message" required><?= $edit_announcement ? htmlspecialchars($edit_announcement['message']) : '' ?></textarea>

    <button type="submit" name="<?= $edit_announcement ? 'edit_announcement' : 'add_announcement' ?>">
        <?= $edit_announcement ? "Update Announcement" : "Add Announcement" ?>
    </button>
    <?php if ($edit_announcement): ?>
        <a href="manage_announcements.php" style="margin-left:15px;">Cancel</a>
    <?php endif; ?>
</form>

<h2>All Announcements</h2>
<table>
    <thead>
        <tr><th>Title</th><th>Message</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php while ($row = $announcements->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
            <td>
                <a href="?edit=<?= $row['id'] ?>">Edit</a>
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure to delete this announcement?')" class="delete">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
