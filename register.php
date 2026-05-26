<?php
include 'config.php';
include 'functions.php';

if (isLogged()) { header('Location: dashboard.php'); exit; }

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match("/^[a-zA-Z\s]{2,50}$/u", $name)) {
        $error = "Emri i pavlefshëm!";
    } elseif (!validEmail($email)) {
        $error = "Email-i është i pavlefshëm!";
    } elseif (!empty($phone) && !validPhone($phone)) {
        $error = "Numri i telefonit i pavlefshëm!";
    } elseif (strlen($username) < 3) {
        $error = "Username duhet të ketë së paku 3 karaktere!";
    } elseif (strlen($password) < 6) {
        $error = "Fjalëkalimi duhet të ketë së paku 6 karaktere!";
    } else {
        try {
            global $conn;
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($exists) {
                $error = "Ky username ekziston tashmë!";
            } else {
                $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                $phoneVal   = !empty($phone) ? $phone : null;

                $stmt = $conn->prepare(
                    "INSERT INTO users (username, password, role, name, email, phone)
                     VALUES (?, ?, 'user', ?, ?, ?)"
                );
                $stmt->bind_param('sssss', $username, $hashedPass, $name, $email, $phoneVal);

                if ($stmt->execute()) {
                    $success = "Regjistrimi u krye me sukses! <a href='login.php'>Kyçu tani</a>";
                } else {
                    $error = "Gabim gjatë regjistrimit.";
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "Gabim i brendshëm.";
        }
    }
}

$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Regjistrohu - Tour Guide Prishtina</title>
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
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" style="color:#fff;font-weight:bold;">Regjistrohu</a></li>

        </ul>
    </nav>
    <main>
        <div class="register-box" style="max-width:400px; margin:2rem auto;">
            <h2>Regjistrohu</h2>
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="name" placeholder="Emri i plotë" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="tel" name="phone" placeholder="Telefoni (opsional)">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password (min. 6)" required>
                <button type="submit" name="register">Regjistrohu</button>
            </form>
            <p style="margin-top:1rem; text-align:center;">
                Keni llogari? <a href="login.php">Kyçu këtu</a>
            </p>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>
</body>
</html>