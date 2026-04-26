<?php
include 'config.php';
include 'functions.php';

if(isLogged()) header('Location: dashboard.php');

$error = '';
$success = '';

// LOGIN
if(isset($_POST['login'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];
    
    if(isset($users[$u]) && $users[$u]['pass'] == $p) {
        $_SESSION['user'] = ['username'=>$u, 'role'=>$users[$u]['role'], 'name'=>$users[$u]['name']];
        header('Location: index.php');
        exit;
    } else {
        $error = "Username ose password i gabuar!";
    }
}

// REGISTER
if(isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if(!preg_match("/^[a-zA-Z\s]{2,50}$/", $name)) $error = "Emri i pavlefshëm!";
    elseif(!validEmail($email)) $error = "Email i pavlefshëm!";
    elseif(!empty($phone) && !validPhone($phone)) $error = "Telefoni i pavlefshëm!";
    elseif(strlen($username) < 3) $error = "Username shumë i shkurtër!";
    elseif(strlen($password) < 4) $error = "Password shumë i shkurtër!";
    elseif(isset($users[$username])) $error = "Username ekziston!";
    else {
        // Regjistro user-in e ri
        $users[$username] = [
            'pass' => $password,
            'role' => 'user',
            'name' => $name
        ];
        $_SESSION['users_data'] = $users;
        $success = "Regjistrimi u krye! Tani mund të kyçeni.";
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
        <ul><li><a href="index.php">Home</a></li></ul>
    </nav>
    <main>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 800px; margin: 0 auto;">
            
            <!-- Login Form -->
            <div class="login-box" style="width:100%">
                <h2>Login</h2>
                <?php if($error) echo "<div class='error'>$error</div>"; ?>
                <form method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login">Kyçu</button>
                </form>
                <p style="margin-top:1rem; font-size:0.8rem;">
                    Test: <strong>admin/admin123</strong> | <strong>user/user123</strong>
                </p>
            </div>
            
            <!-- Register Form -->
            <div class="register-box" style="width:100%">
                <h2>Regjistrohu</h2>
                <?php if($success) echo "<div class='success'>$success</div>"; ?>
                <form method="POST">
                    <input type="text" name="name" placeholder="Emri i plotë" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="tel" name="phone" placeholder="Telefoni (opsional)">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="register">Regjistrohu</button>
                </form>
            </div>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

<style>
<?php if($theme == 'dark'): ?>
body { background: #1a1a2e; }
.container { background: #16213e; color: #eee; }
.login-box, .register-box { background: #0f3460; }
input, button { background: #1a1a2e; color: #eee; border-color: #2c5a7a; }
<?php endif; ?>
</style>
</body>
</html>