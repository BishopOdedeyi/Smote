<?php
session_start();
require_once "config.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? "";

// Fetch notes (LIFO)
$stmt = $pdo->prepare("SELECT id, title, content, created_at, is_summary 
                       FROM notes 
                       WHERE user_id = :user_id 
                       ORDER BY created_at DESC");
$stmt->execute([":user_id" => $user_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Welcome, <?= htmlspecialchars($username) ?> 👋</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </header>

    <section class="notes-section">
        <?php if (empty($notes)): ?>
            <p class="no-notes">No notes yet. Click the + button to add one!</p>
        <?php else: ?>
            <div class="notes-grid">
                <?php foreach ($notes as $note): ?>
                    <div class="note-card <?= $note['is_summary'] ? 'summary-note' : '' ?>">
                        <h3><?= htmlspecialchars($note['title']) ?></h3>
                        <p class="note-preview">
                            <?= htmlspecialchars(mb_strimwidth($note['content'], 0, 150, "...")) ?>
                        </p>
                        <div class="note-footer">
                            <small><?= date("M d, Y H:i", strtotime($note['created_at'])) ?></small>
                            <a href="view_note.php?id=<?= $note['id'] ?>" class="view-btn">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Floating Add Button -->
    <a href="create_note.php" class="add-btn">+</a>
</div>
</body>
</html>
