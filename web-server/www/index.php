<?php
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$message = '';
$error = '';

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    // Core logic remains the same (Vulnerable to SQLi)
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = getDB()->query($query);
    
    if ($result && $result->rowCount() > 0) {
        $user = $result->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php?page=dashboard");
        exit();
    } else {
        $error = "Invalid unified credentials. Please verify your identity.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle registration
if (isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $password = md5($_POST['reg_password']);
    $email = $_POST['reg_email'];
    
    try {
        $stmt = getDB()->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $email]);
        $message = "Registration synchronized. You may now authenticate.";
    } catch(PDOException $e) {
        $error = "Synchronization failed: " . $e->getMessage();
    }
}

// Handle comment submission (Vulnerable to XSS)
if (isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
    $post_id = $_POST['post_id'];
    $comment = $_POST['comment'];
    
    $stmt = getDB()->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $_SESSION['user_id'], $comment]);
    $message = "Your contribution has been indexed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFlow Connect | Unified Workspace</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav id="navbar">
        <div class="container nav-content">
            <a href="index.php" class="logo">
                <div class="logo-icon"></div>
                TechFlow <span class="gradient-text">Connect</span>
            </a>
            <div class="nav-links">
                <a href="index.php?page=home" class="<?php echo $page == 'home' ? 'active' : ''; ?>">Solutions</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="index.php?page=posts" class="<?php echo $page == 'posts' ? 'active' : ''; ?>">Knowledge</a>
                    <a href="upload.php">Assets</a>
                    <a href="view.php">Preview</a>
                    <a href="index.php?logout=1" class="btn btn-glass" style="padding: 8px 16px;">Logout</a>
                <?php else: ?>
                    <a href="index.php?page=login" class="<?php echo $page == 'login' ? 'active' : ''; ?>">Sign In</a>
                    <a href="index.php?page=register" class="btn btn-primary" style="padding: 8px 16px;">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if ($page == 'home' && !isset($_SESSION['user_id'])): ?>
        <section class="hero animate-fade">
            <div class="container">
                <h1>The Future of <span class="gradient-text">Tech Flow</span> Is Here</h1>
                <p>Collaborate, accelerate, and innovate with our unified workspace platform. Designed for high-performance teams that demand precision and speed.</p>
                <div style="display: flex; gap: 20px; justify-content: center;">
                    <a href="index.php?page=register" class="btn btn-primary">Start Free Trial</a>
                    <a href="#features" class="btn btn-glass">View Solutions</a>
                </div>
            </div>
        </section>

        <section id="features" style="padding: 100px 0;">
            <div class="container">
                <div style="text-align: center; margin-bottom: 60px;">
                    <h2 style="font-size: 2.5rem; margin-bottom: 16px;">Integrated Intelligence</h2>
                    <p style="color: var(--text-dim);">A complete ecosystem for modern enterprise workflows.</p>
                </div>
                <div class="grid">
                    <div class="card glass">
                        <h3 style="margin-bottom: 10px;">Knowledge Index</h3>
                        <p>Share insights across your entire organization with our lightning-fast internal wiki and documentation engine.</p>
                    </div>
                    <div class="card glass">
                        <h3 style="margin-bottom: 10px;">Asset Repository</h3>
                        <p>Securely store and share project files, designs, and technical specifications with granular access controls.</p>
                    </div>
                    <div class="card glass">
                        <h3 style="margin-bottom: 10px;">Unified Identity</h3>
                        <p>One identity, multiple services. Access all your TechFlow tools with a single, secure authentication layer.</p>
                    </div>
                </div>
            </div>
        </section>
    <?php else: ?>
        <div class="container" style="padding-top: 140px;">
            <?php if ($message) echo "<div class='alert alert-success'>$message</div>"; ?>
            <?php if ($error) echo "<div class='alert alert-error'>$error</div>"; ?>

            <?php
            switch($page) {
                case 'login':
                    ?>
                    <div class="auth-container glass animate-fade">
                        <h2 style="margin-bottom: 30px; text-align: center;">Enterprise Sign In</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label>Unified ID / Username</label>
                                <input type="text" name="username" required placeholder="e.g. admin">
                            </div>
                            <div class="form-group">
                                <label>Access Key / Password</label>
                                <input type="password" name="password" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Authenticate</button>
                        </form>
                    </div>
                    <?php
                    break;

                case 'register':
                    ?>
                    <div class="auth-container glass animate-fade">
                        <h2 style="margin-bottom: 30px; text-align: center;">Account Provisioning</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label>Preferred Username</label>
                                <input type="text" name="reg_username" required>
                            </div>
                            <div class="form-group">
                                <label>Work Email Address</label>
                                <input type="email" name="reg_email" required>
                            </div>
                            <div class="form-group">
                                <label>Establish Password</label>
                                <input type="password" name="reg_password" required>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary" style="width: 100%;">Create Account</button>
                        </form>
                    </div>
                    <?php
                    break;

                case 'dashboard':
                    if (!isset($_SESSION['user_id'])) { header("Location: index.php?page=login"); exit(); }
                    ?>
                    <div class="animate-fade">
                        <h2 style="font-size: 2rem; margin-bottom: 10px;">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>.</h2>
                        <p style="color: var(--text-dim);">Here's what's happening in your workspace today.</p>
                        
                        <div class="grid" style="margin-top: 40px;">
                            <a href="index.php?page=posts" style="text-decoration: none;">
                                <div class="card glass">
                                    <h3 class="gradient-text">Browse Knowledge</h3>
                                    <p>Read and contribute to internal technical documentation.</p>
                                </div>
                            </a>
                            <a href="upload.php" style="text-decoration: none;">
                                <div class="card glass">
                                    <h3 class="gradient-text">Project Assets</h3>
                                    <p>Manage shared resources and project deliverables.</p>
                                </div>
                            </a>
                            <a href="index.php?page=profile" style="text-decoration: none;">
                                <div class="card glass">
                                    <h3 class="gradient-text">My Profile</h3>
                                    <p>Update your professional identity and workspace preferences.</p>
                                </div>
                            </a>
                        </div>

                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <div class="glass" style="margin-top: 50px; padding: 40px;">
                                <h3 style="margin-bottom: 20px;">System Administrator Console</h3>
                                <p style="margin-bottom: 20px; color: var(--text-dim);">Global configuration and user management utilities.</p>
                                <a href="admin.php" class="btn btn-primary">Open Admin Panel</a>
                            </div>
                        <?php endif; ?>

                        <!-- Subtly hidden SSH key for internal-server-1 -->
                        <div style="margin-top: 100px; opacity: 0.1;">
                            <p style="font-size: 0.7rem;">DEBUG_MODE: EXT_AUTH_BRIDGE_ACTIVE</p>
                            <!-- 
                            Legacy Access Key - internal-server-1 maintenance
                            -----BEGIN OPENSSH PRIVATE KEY-----
                            b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAABFwAAAAdzc2gtcn
                            NhAAAAAwEAAQAAAQEAmQpAxHEFkKBE0OOQsciHn4/RLeg2ZK3DjcnA+AlnCjnCtEOUkAzn
                            kp6YUAXJw2P/nsTE7v0BoUtT9/mDXQJfAA76r/OP+0rHHzjjT2e8mQZfzPaydrSqUwDAGi
                            hfFDaTYCkUl16cHh+sv53XXPh5kkDqT2jzFQNi8FZHC91VcZXUABIq71UdmzYEoAgiqDkn
                            M0xcPcpiVK2xSqNMLvuAsJC5s23JBCeJ4mVINJheyCDk6Zgt8FKy0S2zsytoVo/cw6rcWb
                            W8N2U+pT9vWCIFuzQzNw0L+NjnDpt2ZqOX+8+S1eL4ZFbpFe2JFhu4rRSGy0RA1bbwiOYS
                            M9dpQTtGGQAAA9D6zD6M+sw+jAAAAAdzc2gtcnNhAAABAQCZCkDEcQWQoETQ45CxyIefj9
                            Et6DZkrcONycD4CWcKOcK0Q5SQDOeSnphQBcnDY/+exMTu/QGhS1P3+YNdAl8ADvqv84/7
                            SscfOONPZ7yZBl/M9rJ2tKpTAMAaKF8UNpNgKRSXXpweH6y/nddc+HmSQOpPaPMVA2LwVk
                            cL3VVxldQAEirvVR2bNgSgCCKoOSczTFw9ymJUrbFKo0wu+4CwkLmzbckEJ4niZUg0mF7I
                            IOTpmC3wUrLRLbOzK2hWj9zDqtxZtbw3ZT6lP29YIgW7NDM3DQv42OcOm3Zmo5f7z5LV4v
                            hkVukV7YkWG7itFIbLREDVtvCI5hIz12lBO0YZAAAAAwEAAQAAAP9cD87mXRb76W18O2jP
                            idIMKnerJgWSivUYVkW+7+kMPbLivcSg4yJrEPyPJuw4ne8nkzbkjU5tuOLKSU68pLCBWI
                            F1Vw9GG7WtEourOLw7UZpPshepUQUk6U6mbaLEhhAhyJSaek9vz54bptfnFZIMaQCKxJd2
                            DJN4AG68tGix6oCZcbeDGmr34r+y34rC3UY55O1ckwmOxJbfDxaQOUBeUC1wSd7quWlsdh
                            dEvqKjxBwpLASRsHH3IalsdtqOFHoZhjKgFtaf/5ua8ZOraNZ7pVf+ajPz6J0qX+2+p131
                            683D9o7pCYyLTdzVVQH+BF6Ug1BMAEH8pt/Y7HGxqAEAAACBALJXNouo9zI/j8A5K3NAPI
                            g0Ky3FVHo1NPbdnaNmfChV74p/oQ+cbyNfO6CrnvsUbSqxMczEW5YEul//NFRdLF+0/PnI
                            YIizG+Z9FxWE8NUL259sy8qwTmabUB4Krrq2TJPt/F/X6SmyXU/D8ij6kxx0F/SVtSC0z8
                            ma3pftng+vAAAAgQDXppdVjPk6X0/zTGVWH/hs2WY8/UGVb1MVoIcGyo/CPF8/1Z598BOJ
                            zqck68wYG5BL17Hql6Fv5gs5P57VNOC+ahl78X7hVPl5PsKo7zK2FhKJC9mPbQpSIxGdi/
                            1GaECvoR2hGCFUjAK5nl0fDrIcQKX2AM18k1J0tJBlBqKpgQAAAIEAtayvgK12mfjmibSu
                            CPvw4G86pXnh50YX3sH3xFuXRcgIrCYtiD5PTEx5IGin3U7OhT5QWwy2OYTGAdcUbN2jbd
                            fnUtwZ0wxAKcyitMRi1M+MV6adjI7yNgskSbHJCryjOisvMUY7d0/6wfDFg3gb/yi8ZBU4
                            UruLiLgh9edP+JkAAAAVeGh6ZWVtQHhNYWNib29rLmxvY2FsAQIDBAUG
                            -----END OPENSSH PRIVATE KEY-----
                            -->
                        </div>
                    </div>
                    <?php
                    break;

                case 'profile':
                    if (!isset($_SESSION['user_id'])) { header("Location: index.php?page=login"); exit(); }
                    // IDOR vulnerability
                    $user_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];
                    $stmt = getDB()->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        ?>
                        <div class="glass animate-fade">
                            <div class="profile-header">
                                <div class="profile-avatar">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h2 style="font-size: 2.5rem;"><?php echo htmlspecialchars($user['username']); ?></h2>
                                    <p style="color: var(--primary); font-weight: 500;"><?php echo strtoupper($user['role']); ?> Specialist</p>
                                </div>
                            </div>
                            <div style="padding: 0 40px 40px;">
                                <div class="grid" style="grid-template-columns: 1fr 1fr;">
                                    <div>
                                        <label style="color: var(--text-dim); font-size: 0.9rem;">Email Address</label>
                                        <p style="font-size: 1.1rem; margin-bottom: 20px;"><?php echo htmlspecialchars($user['email']); ?></p>
                                        <label style="color: var(--text-dim); font-size: 0.9rem;">Identity Verified Since</label>
                                        <p style="font-size: 1.1rem;"><?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                                    </div>
                                    <div>
                                        <label style="color: var(--text-dim); font-size: 0.9rem;">Security Clearance</label>
                                        <p style="font-size: 1.1rem; margin-bottom: 20px;">Level <?php echo $user['role'] == 'admin' ? '4' : '2'; ?></p>
                                        <label style="color: var(--text-dim); font-size: 0.9rem;">Network Status</label>
                                        <p style="color: var(--primary);">Active</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    break;

                case 'posts':
                    if (!isset($_SESSION['user_id'])) { header("Location: index.php?page=login"); exit(); }
                    ?>
                    <div class="animate-fade">
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
                            <div>
                                <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Knowledge Base</h1>
                                <p style="color: var(--text-dim);">Searchable repository for technical insights and project documentation.</p>
                            </div>
                            <form method="GET" style="display: flex; gap: 10px;">
                                <input type="hidden" name="page" value="posts">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <input type="text" name="search" placeholder="Query documentation..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Search</button>
                            </form>
                        </div>

                        <?php
                        if (isset($_GET['search'])) {
                            $search = $_GET['search'];
                            // Vulnerable to SQLi
                            $query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
                        } else {
                            $query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE is_private = 0";
                        }
                        
                        $posts = getDB()->query($query);
                        
                        if ($posts) {
                            foreach ($posts as $post) {
                                ?>
                                <div class="card glass" style="margin-bottom: 30px;">
                                    <div class="card-meta">BY <?php echo strtoupper(htmlspecialchars($post['username'])); ?> • <?php echo date('M d, Y', strtotime($post['created_at'])); ?></div>
                                    <h3 style="font-size: 1.5rem;"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                                    
                                    <div class="comments-section">
                                        <h4 style="margin-bottom: 15px; font-size: 1rem; color: var(--text-dim);">Collaborator Contributions</h4>
                                        <?php
                                        $stmt = getDB()->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ?");
                                        $stmt->execute([$post['id']]);
                                        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        foreach ($comments as $comment) {
                                            // Vulnerable to Stored XSS
                                            echo "<div class='comment'>";
                                            echo "<div class='comment-user'>" . htmlspecialchars($comment['username']) . "</div>";
                                            echo "<div>" . $comment['comment'] . "</div>";
                                            echo "</div>";
                                        }
                                        ?>
                                        
                                        <form method="POST" style="margin-top: 20px; display: flex; gap: 15px;">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                                                <input type="text" name="comment" placeholder="Add technical insight..." required>
                                            </div>
                                            <button type="submit" name="add_comment" class="btn btn-glass" style="padding: 12px 20px;">Post</button>
                                        </form>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <?php
                    break;
            }
            ?>
        </div>
    <?php endif; ?>

    <footer id="footer">
        <div class="container footer-content">
            <a href="index.php" class="logo" style="justify-content: center; margin-bottom: 20px;">
                <div class="logo-icon"></div>
                TechFlow <span class="gradient-text">Connect</span>
            </a>
            <p style="color: var(--text-dim); font-size: 0.9rem;">© 2024 TechFlow Connect Corporation. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Terms of Service</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Unified Security</a>
                <a href="#">Contact Support</a>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>

