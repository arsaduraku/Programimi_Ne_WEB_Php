<?php
include 'config.php';
include 'functions.php';

$message = '';
$error   = '';
$theme   = $_COOKIE['theme'] ?? 'light';

if (isset($_POST['send'])) {

    // Merr vlerat dhe hiq hapesira
    $cname    = trim($_POST['contact_name']  ?? '');
    $cemail   = trim($_POST['contact_email'] ?? '');
    $subject  = trim($_POST['subject']       ?? '');
    $body     = trim($_POST['body']          ?? '');

    if (empty($cname) || !preg_match("/^[a-zA-Z\s]{2,50}$/u", $cname)) {
        $error = "Emri i pavlefshëm! (vetëm shkronja, 2–50 karaktere)";
    } elseif (!validEmail($cemail)) {
        $error = "Email-i është i pavlefshëm!";
    } elseif (empty($subject) || strlen($subject) < 3) {
        $error = "Subjekti duhet të ketë së paku 3 karaktere!";
    } elseif (empty($body) || strlen($body) < 10) {
        $error = "Mesazhi duhet të ketë së paku 10 karaktere!";
    } else {

        try {
            $safeName    = htmlspecialchars($cname,   ENT_QUOTES, 'UTF-8');
            $safeEmail   = filter_var($cemail,        FILTER_SANITIZE_EMAIL);
            $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
            $safeBody    = htmlspecialchars($body,    ENT_QUOTES, 'UTF-8');

            // Dergim email 
            $to      = 'admin@tourguide.com';  
            $headers = implode("\r\n", [
                "From: noreply@tourguide-prishtina.com",
                "Reply-To: $safeEmail",
                "MIME-Version: 1.0",
                "Content-Type: text/plain; charset=UTF-8",
                "X-Mailer: PHP/" . phpversion()
            ]);
            $fullMsg = "Emri  : $safeName\nEmail : $safeEmail\nSubjekti: $safeSubject\n\n$safeBody";

            $sent = mail($to, $safeSubject, $fullMsg, $headers);

            // ruaj ne file si backup
            $logLine = date('Y-m-d H:i:s') . " | $safeName | $safeEmail | $safeSubject\n";
            file_put_contents('contact_messages.log', $logLine, FILE_APPEND | LOCK_EX);

            $message = $sent
                ? "Mesazhi u dërgua me sukses! Do t'ju kontaktojmë së shpejti."
                : "Mesazhi u regjistrua! Do t'ju kontaktojmë së shpejti.";

        } catch (Exception $e) {
            error_log(date('Y-m-d H:i:s') . " [CONTACT] " . $e->getMessage() . "\n", 3, 'errors.log');
            $error = "Gabim gjatë dërgimit. Provoni përsëri.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kontakti - Tour Guide Prishtina</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <div>Tour Guide Prishtina</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <?php if(isLogged()): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if(hasRole('admin')): ?>
                    <li><a href="admin_tours.php">Edit Tours</a></li>
                    <li><a href="admin_users.php">Admin</a></li>
                <?php endif; ?>
                    <li><a href="contact.php" style="color:#fff;font-weight:bold;">Kontakti</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
                <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>

    <main>
        <h2>Na Kontaktoni</h2>

        <?php if ($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem; max-width:900px; margin:2rem auto;">

            <!-- FORMA -->
            <div class="login-box" style="max-width:100%; margin:0;">
                <h3>Dërgo Mesazh</h3>
                <form method="POST">
                    <label style="display:block;margin-top:.5rem;font-weight:bold;">Emri i plotë *</label>
                    <input type="text" name="contact_name" placeholder="p.sh. Arta Gashi"
                           value="<?php echo htmlspecialchars($_POST['contact_name'] ?? ''); ?>" required>

                    <label style="display:block;margin-top:.5rem;font-weight:bold;">Email *</label>
                    <input type="email" name="contact_email" placeholder="email@example.com"
                           value="<?php echo htmlspecialchars($_POST['contact_email'] ?? ''); ?>" required>

                    <label style="display:block;margin-top:.5rem;font-weight:bold;">Subjekti *</label>
                    <input type="text" name="subject" placeholder="Pyetje për tourin..."
                           value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>

                    <label style="display:block;margin-top:.5rem;font-weight:bold;">Mesazhi *</label>
                    <textarea name="body" rows="5"
                        style="width:100%;padding:.5rem;margin:.5rem 0;border:1px solid #d1d5db;
                               border-radius:5px;font-family:Arial;font-size:14px;"
                        placeholder="Shkruani mesazhin tuaj këtu..."><?php
                            echo htmlspecialchars($_POST['body'] ?? '');
                    ?></textarea>

                    <button type="submit" name="send" style="width:100%;margin-top:.5rem;">
                        Dërgo Mesazhin
                    </button>
                </form>
            </div>

            <!-- INFO KONTAKTI -->
            <div>
                <div class="stat-card" style="margin-bottom:1rem;">
                    <h3>Adresa</h3>
                    <p style="margin-top:.5rem;">Rruga "Nënë Tereza" Nr. 5<br>10000 Prishtinë, Kosovë</p>
                </div>
                <div class="stat-card" style="margin-bottom:1rem;">
                    <h3>Telefoni</h3>
                    <p style="margin-top:.5rem;">+383 44 000 000<br>+383 49 000 001</p>
                </div>
                <div class="stat-card" style="margin-bottom:1rem;">
                    <h3>Email</h3>
                    <p style="margin-top:.5rem;">info@tourguide-prishtina.com</p>
                </div>
                <div class="stat-card">
                    <h3>Orari</h3>
                    <p style="margin-top:.5rem;">E Hënë – E Shtunë<br>08:00 – 18:00</p>
                </div>
            </div>
        </div>
    </main>

    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

<style>
<?php if($theme == 'dark'): ?>
body { background: #1a1a2e; }
.container { background: #16213e; color: #eee; }
.login-box { background: #0f3460; }
.stat-card { background: #0f3460; }
input, textarea, button { background: #1a1a2e; color: #eee; border-color: #2c5a7a; }
label { color: #ccc; }
<?php endif; ?>
</style>
</body>
</html>