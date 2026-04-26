<?php
include 'config.php';
include 'functions.php';
require_once 'tour.php';

// Vetem admini ka qasje
requireAdmin();

$theme = $_COOKIE['theme'] ?? 'light';
$message = '';
$error = '';

// Shto tour te ri
if(isset($_POST['add_tour'])) {
    $name = trim($_POST['name']);
    $hours = (float)$_POST['hours'];
    $price = (float)$_POST['price'];
    $spots = (int)$_POST['spots'];
    
    if(empty($name)) {
        $error = "Emri i turit është i detyrueshëm!";
    } elseif($hours <= 0 || $price <= 0 || $spots <= 0) {
        $error = "Të gjitha vlerat duhet të jenë pozitive!";
    } else {
        $newId = count($_SESSION['tours']) + 1;
        $_SESSION['tours'][] = [
            'id' => $newId,
            'name' => $name,
            'hours' => $hours,
            'price' => $price,
            'spots' => $spots
        ];
        $tours = $_SESSION['tours'];
        $message = "Turi '{$name}' u shtua me sukses!";
    }
}

// Fshi tour
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    foreach($_SESSION['tours'] as $key => $tour) {
        if($tour['id'] == $id) {
            unset($_SESSION['tours'][$key]);
            $_SESSION['tours'] = array_values($_SESSION['tours']);
            $tours = $_SESSION['tours'];
            $message = "Turi u fshi me sukses!";
            break;
        }
    }
}

// Ndrysho tour
if(isset($_POST['edit_tour'])) {
    $id = (int)$_POST['tour_id'];
    $name = trim($_POST['name']);
    $hours = (float)$_POST['hours'];
    $price = (float)$_POST['price'];
    $spots = (int)$_POST['spots'];
    
    foreach($_SESSION['tours'] as $key => $tour) {
        if($tour['id'] == $id) {
            $_SESSION['tours'][$key] = [
                'id' => $id,
                'name' => $name,
                'hours' => $hours,
                'price' => $price,
                'spots' => $spots
            ];
            $tours = $_SESSION['tours'];
            $message = "Turi '{$name}' u ndryshua me sukses!";
            break;
        }
    }
}

$tours = $_SESSION['tours'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menaxho Turet</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <div>Tour Guide Prishtina</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="admin_tours.php">Admin Panel</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h2>Menaxho Turet</h2>
        
        <?php if($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Forma për shtimin e turit -->
        <div class="admin-box" style="margin-bottom: 2rem;">
            <h3>Shto një tur të ri</h3>
            <form method="POST">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                    <input type="text" name="name" placeholder="Emri i turit" required>
                    <input type="number" name="hours" step="0.5" placeholder="Orët" required>
                    <input type="number" name="price" step="0.5" placeholder="Çmimi (€)" required>
                    <input type="number" name="spots" placeholder="Vendet" required>
                </div>
                <button type="submit" name="add_tour" style="margin-top: 1rem;">Shto Tur</button>
            </form>
        </div>
        
        <!-- Lista e tureve ekzistuese -->
        <h3>Turet Ekzistuese</h3>
        <div class="tours">
            <?php foreach($tours as $tour):
            // KRIJOJMË OBJEKT TOUR - PA NDRYSHUAR LOGJIKËN E TJETËR
            $tourObj = new Tour($tour['name'], $tour['price'], $tour['hours'], $tour['spots']);
            ?>
            <div class="tour-card">
                <h3><?php echo htmlspecialchars($tourObj->getName()); ?></h3>
                <p><?php echo $tourObj->getHours(); ?> orë</p>
                <p class="price">€<?php echo $tourObj->getPrice(); ?> / person</p>
                <p>Vende: <?php echo $tourObj->getSpots(); ?></p>

                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <a href="?delete=<?php echo $tour['id']; ?>" class="btn" style="background:#8b0000;">Fshi</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

<style>
<?php if($theme == 'dark'): ?>
body { background: #1a1a2e; }
.container { background: #16213e; color: #eee; }
.admin-box { background: #0f3460; }
.tour-card { background: #0f3460; }
input, select, button { background: #1a1a2e; color: #eee; border-color: #2c5a7a; }
<?php endif; ?>
</style>
</body>
</html>