<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

$message = '';
$error = '';

if (isset($_POST['upload'])) {
    $target_dir = "uploads/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    
    // Core logic remains the same (Vulnerable to unrestricted upload)
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $message = "Asset synchronized successfully. External reference: <a href='$target_file' style='color: var(--primary);'>$target_file</a>";
    } else {
        $error = "Synchronization failed. Please verify internal storage availability.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Repository | TechFlow Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="padding-top: 100px;">
    <nav class="scrolled">
        <div class="container nav-content">
            <a href="index.php" class="logo">
                <div class="logo-icon"></div>
                TechFlow <span class="gradient-text">Connect</span>
            </a>
            <div class="nav-links">
                <a href="index.php?page=dashboard">Dashboard</a>
                <a href="index.php?page=posts">Knowledge</a>
                <a href="upload.php" class="active">Assets</a>
                <a href="view.php">Preview</a>
                <a href="index.php?logout=1" class="btn btn-glass" style="padding: 8px 16px;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container animate-fade">
        <div style="margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Project Asset Repository</h1>
            <p style="color: var(--text-dim);">Centralized storage for technical specifications, design documents, and project deliverables.</p>
        </div>

        <?php if ($message) echo "<div class='alert alert-success'>$message</div>"; ?>
        <?php if ($error) echo "<div class='alert alert-error'>$error</div>"; ?>

        <div class="grid" style="grid-template-columns: 1fr 1fr;">
            <div class="glass" style="padding: 40px;">
                <h3 style="margin-bottom: 20px;">Synchronize New Asset</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group" style="border: 2px dashed var(--border-glass); padding: 40px; border-radius: 16px; text-align: center; cursor: pointer; transition: var(--transition);">
                        <label for="file-upload" style="cursor: pointer;">
                            <div style="font-size: 3rem; margin-bottom: 10px; opacity: 0.5;">📁</div>
                            <p style="color: var(--text-dim);">Select files or drag and drop to index</p>
                            <input type="file" name="file" id="file-upload" required style="display: none;">
                        </label>
                    </div>
                    <div id="file-info" style="margin-top: 15px; font-size: 0.9rem; color: var(--primary); display: none;"></div>
                    <button type="submit" name="upload" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Upload & Index Asset</button>
                </form>
            </div>

            <div class="glass" style="padding: 40px;">
                <h3 style="margin-bottom: 20px;">Repository Guidelines</h3>
                <ul style="list-style: none; color: var(--text-dim);">
                    <li style="margin-bottom: 15px; border-bottom: 1px solid var(--border-glass); padding-bottom: 15px;">
                        <strong>Supported Classes:</strong> Any binary or text format is supported for universal compatibility.
                    </li>
                    <li style="margin-bottom: 15px; border-bottom: 1px solid var(--border-glass); padding-bottom: 15px;">
                        <strong>Storage Limit:</strong> 2GB per asset. Total account quota: Unlimited.
                    </li>
                    <li style="margin-bottom: 15px;">
                        <strong>Visibility:</strong> Logged assets are visible to your entire organizational unit by default.
                    </li>
                </ul>
            </div>
        </div>

        <div class="glass" style="margin-top: 40px; padding: 40px;">
            <h3 style="margin-bottom: 20px;">Recent Activity</h3>
            <div style="color: var(--text-dim); text-align: center; padding: 40px;">
                No recent asset modifications detected for your current session.
            </div>
        </div>
    </div>

    <footer style="margin-top: 100px;">
        <div class="container footer-content">
            <p style="color: var(--text-dim); font-size: 0.9rem;">© 2024 TechFlow Connect Corporation. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.getElementById('file-upload').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const infoDiv = document.getElementById('file-info');
            if (fileName) {
                infoDiv.textContent = 'Selected: ' + fileName;
                infoDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>

