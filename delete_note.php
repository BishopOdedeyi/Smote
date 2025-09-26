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

// Check if note exists and belongs to user
$stmt = $pdo->prepare("SELECT id FROM notes WHERE id = :id AND user_id = :user_id LIMIT 1");
$stmt->execute([":id" => $note_id, ":user_id" => $user_id]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    header("Location: index.php");
    exit;
}

// Delete note
$stmt = $pdo->prepare("DELETE FROM notes WHERE id = :id AND user_id = :user_id");
$stmt->execute([":id" => $note_id, ":user_id" => $user_id]);

// Redirect to dashboard (index.php)
header("Location: index.php");
exit;
