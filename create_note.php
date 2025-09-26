<?php
session_start();
require_once "config.php";
require __DIR__ . '/vendor/autoload.php'; // Guzzle for OCR API

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $method = $_POST["method"];
    $user_id = $_SESSION['user_id'];

    // ======================
    // CASE 1: TYPED NOTE
    // ======================
    if ($method === "typed") {
        $title = trim($_POST["title"]);
        $content = trim($_POST["content"]);

        if (empty($title) || empty($content)) {
            $errors[] = "Both title and content are required.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content) VALUES (:user_id, :title, :content)");
            $stmt->execute([":user_id" => $user_id, ":title" => $title, ":content" => $content]);
            header("Location: index.php");
            exit;
        }
    }

    // ======================
    // CASE 2: PDF UPLOAD
    // ======================
    elseif ($method === "pdf") {
        if (!isset($_FILES["pdf"]) || $_FILES["pdf"]["error"] !== UPLOAD_ERR_OK) {
            $errors[] = "Please select a valid PDF file.";
        } else {
            $file = $_FILES["pdf"];

            if ($file["size"] > 1024 * 1024) {
                $errors[] = "PDF must not exceed 1MB (POC limit).";
            } elseif (mime_content_type($file["tmp_name"]) !== "application/pdf") {
                $errors[] = "Only PDF files are allowed.";
            } else {
                // Count pages (simple check)
                $pageCount = preg_match_all("/\/Type\s*\/Page[^s]/", file_get_contents($file["tmp_name"]));
                if ($pageCount > 3) {
                    $errors[] = "PDF exceeds 3-page limit.";
                } else {
                    $filename = time() . "_" . basename($file["name"]);
                    $filepath = "uploads/" . $filename;
                    move_uploaded_file($file["tmp_name"], $filepath);

                    // Run OCR
                    $ocrText = runOcrSpace($filepath);

                    // Save note with OCR text
                    $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content) VALUES (:user_id, :title, :content)");
                    $stmt->execute([
                        ":user_id" => $user_id,
                        ":title"   => "Uploaded PDF: " . $file["name"],
                        ":content" => $ocrText ?: "[OCR failed or empty]"
                    ]);
                    $note_id = $pdo->lastInsertId();

                    // Save file reference
                    $stmt = $pdo->prepare("INSERT INTO files (user_id, note_id, filename, mime, size_bytes, status) 
                                           VALUES (:user_id, :note_id, :filename, :mime, :size, 'uploaded')");
                    $stmt->execute([
                        ":user_id"  => $user_id,
                        ":note_id"  => $note_id,
                        ":filename" => $filename,
                        ":mime"     => "application/pdf",
                        ":size"     => $file["size"]
                    ]);

                    header("Location: index.php");
                    exit;
                }
            }
        }
    }

    // ======================
    // CASE 3: BULK IMAGES
    // ======================
    elseif ($method === "images") {
        if (!isset($_FILES["images"])) {
            $errors[] = "Please select images to upload.";
        } else {
            $files = $_FILES["images"];
            $count = count($files["name"]);

            if ($count > 10) {
                $errors[] = "You can upload up to 10 images at a time.";
            } else {
                $allOcrText = "";

                $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content) VALUES (:user_id, :title, :content)");
                $stmt->execute([
                    ":user_id" => $user_id,
                    ":title"   => "Uploaded Images (" . $count . ")",
                    ":content" => "[OCR processing pending]"
                ]);
                $note_id = $pdo->lastInsertId();

                for ($i = 0; $i < $count; $i++) {
                    if ($files["error"][$i] === UPLOAD_ERR_OK) {
                        $file_size = $files["size"][$i];
                        $file_type = mime_content_type($files["tmp_name"][$i]);

                        if ($file_size > 1024 * 1024) {
                            $errors[] = $files["name"][$i] . " exceeds 1MB (POC limit).";
                            continue;
                        }

                        if (!in_array($file_type, ["image/jpeg", "image/png", "image/jpg"])) {
                            $errors[] = $files["name"][$i] . " is not a valid image.";
                            continue;
                        }

                        $filename = time() . "_" . basename($files["name"][$i]);
                        $filepath = "uploads/" . $filename;
                        move_uploaded_file($files["tmp_name"][$i], $filepath);

                        // Run OCR on image
                        $ocrText = runOcrSpace($filepath);
                        if ($ocrText) {
                            $allOcrText .= "\n---\n" . $files["name"][$i] . ":\n" . $ocrText;
                        }

                        $stmt = $pdo->prepare("INSERT INTO files (user_id, note_id, filename, mime, size_bytes, status) 
                                               VALUES (:user_id, :note_id, :filename, :mime, :size, 'uploaded')");
                        $stmt->execute([
                            ":user_id"  => $user_id,
                            ":note_id"  => $note_id,
                            ":filename" => $filename,
                            ":mime"     => $file_type,
                            ":size"     => $file_size
                        ]);
                    }
                }

                // Update note with extracted OCR
                if ($allOcrText) {
                    $stmt = $pdo->prepare("UPDATE notes SET content = :content WHERE id = :id");
                    $stmt->execute([
                        ":content" => $allOcrText,
                        ":id"      => $note_id
                    ]);
                }

                header("Location: index.php");
                exit;
            }
        }
    }
}

// ======================
// OCR.Space Helper
// ======================
function runOcrSpace($filePath) {
    $client = new \GuzzleHttp\Client();
    $fileData = fopen($filePath, 'r');

    try {
        $r = $client->request('POST', 'https://api.ocr.space/parse/image', [
            'headers' => ['apikey' => 'helloworld'], // TODO: replace with your real key
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $fileData
                ],
                [
                    'name' => 'language',
                    'contents' => 'eng'
                ]
            ]
        ]);

        $response = json_decode($r->getBody(), true);

        if (!empty($response['ParsedResults'])) {
            $parsed = "";
            foreach ($response['ParsedResults'] as $res) {
                $parsed .= $res['ParsedText'] . "\n";
            }
            return trim($parsed);
        }
        return null;
    } catch (Exception $e) {
        return null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Note</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Create a New Note</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="tab-buttons">
        <button type="button" onclick="showTab('typed')">✏️ Typed</button>
        <button type="button" onclick="showTab('pdf')">📄 PDF</button>
        <button type="button" onclick="showTab('images')">🖼 Images</button>
    </div>

    <!-- Typed Note -->
    <form method="POST" action="" id="typed" class="tab-content">
        <input type="hidden" name="method" value="typed">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Content</label>
            <textarea name="content" rows="8" required></textarea>
        </div>
        <button type="submit" class="btn">Save Note</button>
    </form>

    <!-- PDF Upload -->
    <form method="POST" action="" enctype="multipart/form-data" id="pdf" class="tab-content hidden">
        <input type="hidden" name="method" value="pdf">
        <div class="form-group">
            <label>Upload PDF (max 1MB, 3 pages)</label>
            <input type="file" name="pdf" accept="application/pdf" required>
        </div>
        <button type="submit" class="btn">Upload PDF</button>
    </form>

    <!-- Images Upload -->
    <form method="POST" action="" enctype="multipart/form-data" id="images" class="tab-content hidden">
        <input type="hidden" name="method" value="images">
        <div class="form-group">
            <label>Upload up to 10 images (max 1MB each)</label>
            <input type="file" name="images[]" accept="image/*" multiple required>
        </div>
        <button type="submit" class="btn">Upload Images</button>
    </form>

    <p class="switch-link"><a href="index.php">⬅ Back to Dashboard</a></p>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll(".tab-content").forEach(el => el.classList.add("hidden"));
    document.getElementById(tab).classList.remove("hidden");
}
</script>
</body>
</html>
