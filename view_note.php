<?php
session_start();
require_once "config.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validate note id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$note_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch note
$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = :id AND user_id = :user_id LIMIT 1");
$stmt->execute([":id" => $note_id, ":user_id" => $user_id]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    header("Location: index.php");
    exit;
}

// Fetch attached files
$stmt = $pdo->prepare("SELECT * FROM files WHERE note_id = :note_id AND user_id = :user_id");
$stmt->execute([":note_id" => $note_id, ":user_id" => $user_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($note['title']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="note-container">
    <h2><?= htmlspecialchars($note['title']) ?></h2>
    <p class="note-date">Created on: <?= htmlspecialchars($note['created_at']) ?></p>
    <div class="note-content">
        <?= nl2br(htmlspecialchars($note['content'])) ?>
    </div>

    <?php if ($files): ?>
        <div class="attachments">
            <h3>📎 Attachments</h3>
            <ul>
                <?php foreach ($files as $file): ?>
                    <li>
                        <?php if (strpos($file['mime'], 'image') !== false): ?>
                            <img src="uploads/<?= htmlspecialchars($file['filename']) ?>" 
                                 alt="Image Attachment" 
                                 class="note-image">
                        <?php elseif ($file['mime'] === 'application/pdf'): ?>
                            <a href="uploads/<?= htmlspecialchars($file['filename']) ?>" target="_blank">
                                📄 View PDF: <?= htmlspecialchars($file['filename']) ?>
                            </a>
                        <?php else: ?>
                            <a href="uploads/<?= htmlspecialchars($file['filename']) ?>" target="_blank">
                                📂 <?= htmlspecialchars($file['filename']) ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="note-actions">
        <a href="edit_note.php?id=<?= $note['id'] ?>" class="btn">✏️ Edit</a>
        <a href="delete_note.php?id=<?= $note['id'] ?>" 
        class="btn btn-danger"
        onclick="return confirm('Are you sure you want to delete this note?');">🗑 Delete</a>
    </div>


    <p class="switch-link"><a href="index.php">⬅ Back to Dashboard</a></p>
</div>
</body>
</html>
