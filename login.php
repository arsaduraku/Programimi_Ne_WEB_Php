<?php
include 'config.php';
include 'functions.php';

if(isLogged()) {header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// LOGIN
if(isset($_POST['login'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];

    try {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $u);
        $stmt->execute();
        $dbUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($dbUser && password_verify($p, $dbUser['password'])) {
            $_SESSION['user'] = [
                'id'       => $dbUser['id'],
                'username' => $dbUser['username'],
                'role'     => $dbUser['role'],
                'name'     => $dbUser['name']
            ];
        header('Location: index.php');
        exit;
    } else {
        $error = "Username ose password i gabuar!";
    }
   }catch (Exception $e) {
        $error = "Gabim i brendshëm.";
    }
}

$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Tour Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <div>Tour Guide Prishtina</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>            
            <li><a href="contact.php">Kontakti</a></li>
            <li><a href="login.php" style="color:#fff;font-weight:bold;">Login</a></li>
            <li><a href="register.php">Regjistrohu</a></li>
        </ul>
    </nav>
    <main>
        <div class="login-box" style="max-width:350px; margin:2rem auto;">
            <h2>Kyçu në llogarinë tuaj</h2>
            
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Kyçu</button>
            </form>

            <p style="margin-top:1rem; text-align:center;">
                Nuk keni llogari? <a href="register.php">Regjistrohu këtu</a>
            </p>

            <hr style="margin:1rem 0;">

            <p style="font-size:.8rem; text-align:center;">
                Test: <strong>admin / admin123</strong> | <strong>user / user123</strong>
            </p>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

<style>
<?php if($theme == 'dark'): ?>
body { background: #1a1a2e; }
.container { background: #16213e; color: #eee; }
.login-box { background: #0f3460; }
input, button { background: #1a1a2e; color: #eee; border-color: #2c5a7a; }
<?php endif; ?>
</style>
</body>
</html>