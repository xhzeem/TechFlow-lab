<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

$content = '';
$error = '';

// Core logic remains the same (Vulnerable to LFI/Path Traversal)
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
    } else {
        $error = "Document trace failed. Reference not found in primary or secondary volumes.";
    }
}

// Subpage inclusion vulnerability
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    include($page . ".php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Previewer | TechFlow Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/jetbrains-mono@1.0.6/css/jetbrains-mono.min.css" rel="stylesheet">
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
                <a href="upload.php">Assets</a>
                <a href="view.php" class="active">Preview</a>
                <a href="index.php?logout=1" class="btn btn-glass" style="padding: 8px 16px;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container animate-fade">
        <div style="margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Universal Document Previewer</h1>
            <p style="color: var(--text-dim);">Technical diagnostic tool for inspecting system logs, source files, and configuration telemetry.</p>
        </div>

        <div class="grid" style="grid-template-columns: 350px 1fr;">
            <div>
                <div class="glass" style="padding: 30px; margin-bottom: 30px;">
                    <h3 style="margin-bottom: 20px; font-size: 1.1rem;">Trace Document</h3>
                    <form method="GET">
                        <div class="form-group">
                            <label>File Reference / Path</label>
                            <input type="text" name="file" placeholder="e.g. config.php" 
                                   value="<?php echo isset($_GET['file']) ? htmlspecialchars($_GET['file']) : ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Inspect Reference</button>
                    </form>
                </div>

                <div class="glass" style="padding: 30px;">
                    <h3 style="margin-bottom: 20px; font-size: 1.1rem;">Recent Traces</h3>
                    <ul style="list-style: none; color: var(--text-dim); font-size: 0.9rem;">
                        <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--primary);">•</span> <a href="?file=config.php" style="color: inherit; text-decoration: none;">config.php</a>
                        </li>
                        <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--primary);">•</span> <a href="?file=/var/log/apache2/access.log" style="color: inherit; text-decoration: none;">system_access.log</a>
                        </li>
                        <li style="display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--primary);">•</span> <a href="?file=index.php" style="color: inherit; text-decoration: none;">primary_handler.php</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="preview-window">
                <div class="preview-header">
                    <div class="dot dot-red"></div>
                    <div class="dot dot-yellow"></div>
                    <div class="dot dot-green"></div>
                    <span style="margin-left: 10px; font-size: 0.8rem; color: var(--text-dim); font-family: monospace;">
                        <?php echo isset($_GET['file']) ? htmlspecialchars($_GET['file']) : 'terminal.stream'; ?>
                    </span>
                </div>
                <div class="preview-content">
                    <?php if ($error): ?>
                        <div style="color: #ff5f56;"><?php echo htmlspecialchars($error); ?></div>
                    <?php elseif ($content): ?>
                        <?php echo htmlspecialchars($content); ?>
                    <?php else: ?>
                        <div style="color: var(--text-dim); opacity: 0.5;">[WAITING FOR TRACE INPUT]</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer style="margin-top: 100px;">
        <div class="container footer-content">
            <p style="color: var(--text-dim); font-size: 0.9rem;">© 2024 TechFlow Connect Corporation. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

