<?php
session_start();
require_once "config.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$note_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch note (only if belongs to logged in user)
$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = :id AND user_id = :user_id LIMIT 1");
$stmt->execute([":id" => $note_id, ":user_id" => $user_id]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    header("Location: index.php");
    exit;
}

$errors = [];
$title = $note['title'];
$content = $note['content'];

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if (empty($title) || empty($content)) {
        $errors[] = "Both title and content are required.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE notes 
             SET title = :title, content = :content 
             WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute([
            ":title"   => $title,
            ":content" => $content,
            ":id"      => $note_id,
            ":user_id" => $user_id
        ]);

        header("Location: view_note.php?id=" . $note_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Note</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Edit Note</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>
        </div>

        <div class="form-group">
            <label>Content</label>
            <textarea name="content" rows="8" required><?= htmlspecialchars($content) ?></textarea>
        </div>

        <button type="submit" class="btn">Update Note</button>
        <p class="switch-link"><a href="view_note.php?id=<?= $note['id'] ?>">⬅ Back to Note</a></p>
    </form>
</div>
</body>
</html>
