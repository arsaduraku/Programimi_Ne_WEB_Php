<?php
$page_title = "Home";
include 'config.php';

$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tour Guide Prishtina</title>
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
                    <li><a href="admin_tours.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="logout">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <main>
        <div style="background: #0d317e; color: #f2d6d6; padding: 2rem; text-align: center; border-radius: 10px;">       
            <h1>Mirë se vini në Prishtinë!</h1>
            <p>Eksploroni kryeqytetin me guidat tona profesionale</p>
            <a href="tours.php" class="btn" style="display: inline-block; margin-top: 1rem;">Shiko Turet</a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-top: 2rem; margin: 12px;">
            <div style="background:#f9f9f9; padding:25px; border-radius:10px;">
                <h3>Ture Historike</h3>
                <p>Eksploro monumentet</p>
                <img src="foto/newborn.jpg" style="width:100%; margin-top:10px; border-radius:10px;">
            </div>
            <div style="background:#f9f9f9; padding:25px; border-radius:10px;">
                <h3>Gastronomi</h3>
                <p>Shijo ushqimet tradicionale</p>
                <img src="foto/gastronomia.jpg" style="width:100%; margin-top:10px; border-radius:10px;">
            </div>
            <div style="background:#f9f9f9; padding:25px; border-radius:10px;">
                <h3>Night Life</h3>
                <p>Përjeto Prishtinën ndryshe</p>
                <img src="foto/nightlife.jpg" style="width:100%; margin-top:10px; border-radius:10px;">
            </div>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>


<style>
<?php if($theme == 'dark'): ?>
body { background: #1a1a2e; }
.container { background: #16213e; color: #eee; }
main > div:last-child div { background: #0f3460 !important; color: #eee; }
<?php endif; ?>
</style>
</body>
</html>