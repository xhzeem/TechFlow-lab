<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied: Administrative Authorization Required.");
}

$output = '';
$error = '';

// Command injection remains (ICMP Connectivity Bridge)
if (isset($_POST['ping'])) {
    $host = $_POST['host'];
    $command = "ping -c 4 " . $host;
    $output = shell_exec($command);
}

// SSRF remains (Bilateral Proxy Synchronizer)
if (isset($_POST['fetch_url'])) {
    $url = $_POST['url'];
    $content = file_get_contents($url);
    $output = htmlspecialchars($content);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infrastructure Console | TechFlow Connect</title>
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
                <a href="upload.php">Assets</a>
                <a href="view.php">Preview</a>
                <a href="index.php?logout=1" class="btn btn-glass" style="padding: 8px 16px;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container animate-fade">
        <div style="margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Global Infrastructure Console</h1>
            <p style="color: var(--text-dim);">Low-level system orchestration and network telematics for global node management.</p>
        </div>

        <div class="grid">
            <div class="glass" style="padding: 40px;">
                <h3 style="margin-bottom: 20px;">ICMP Connectivity Bridge</h3>
                <p style="color: var(--text-dim); font-size: 0.9rem; margin-bottom: 20px;">Validate latency and packet persistence between the central hub and edge nodes.</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Target Node Identifier / IP</label>
                        <input type="text" name="host" placeholder="e.g. edge-eu-01" required>
                    </div>
                    <button type="submit" name="ping" class="btn btn-primary" style="width: 100%;">Initiate Bridge</button>
                </form>
            </div>

            <div class="glass" style="padding: 40px;">
                <h3 style="margin-bottom: 20px;">Bilateral Proxy Synchronizer</h3>
                <p style="color: var(--text-dim); font-size: 0.9rem; margin-bottom: 20px;">Synchronize internal state with federated external endpoints or internal microservices.</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Endpoint URL / Service Logic</label>
                        <input type="text" name="url" placeholder="http://internal-api.cluster.local" required>
                    </div>
                    <button type="submit" name="fetch_url" class="btn btn-primary" style="width: 100%;">Sync Endpoint</button>
                </form>
            </div>
        </div>

        <?php if ($output): ?>
            <div class="glass" style="margin-top: 40px; border-color: var(--primary);">
                <div style="padding: 15px 25px; border-bottom: 1px solid var(--border-glass); background: rgba(78, 204, 163, 0.05);">
                    <h3 style="font-size: 1.1rem; color: var(--primary);">Telemetry Output Stream</h3>
                </div>
                <div class="preview-window" style="border: none; border-radius: 0;">
                    <div class="preview-content" style="max-height: 500px; color: #4ecca3;">
                        <?php echo $output; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer style="margin-top: 100px;">
        <div class="container footer-content">
            <p style="color: var(--text-dim); font-size: 0.9rem;">© 2024 TechFlow Connect Corporation. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
