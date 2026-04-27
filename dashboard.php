<?php
include 'config.php';
include 'functions.php';
requireLogin();

$theme = $_COOKIE['theme'] ?? 'light';
if(isset($_POST['theme'])) {
    setcookie('theme', $_POST['theme'], time() + 86400 * 30);
    header('Location: dashboard.php');
    exit;
}

$myBookings = getUserBookings($_SESSION['user']['username']);
usort($myBookings, function($a, $b) {
    return $b['total'] <=> $a['total'];
});

$totalSpent = 0;
foreach($myBookings as $b) $totalSpent += $b['total'];

// Per admin, merr te gjitha rezervimet
$allBookings = [];
if(hasRole('admin')) {
    $allBookings = getAllBookings();
}

// Ndrysho statusin e rezervimit (vetem admin)
if(isset($_POST['update_status']) && hasRole('admin')) {
    $bookingIndex = $_POST['booking_index'];
    $newStatus = $_POST['status'];
    if(updateBookingStatus($bookingIndex, $newStatus)) {
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Tour Guide</title>
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
            <?php if(hasRole('admin')): ?>
                <li><a href="admin_tours.php">Admin Panel</a></li>
            <?php endif; ?>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>
    <main>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Dashboard</h2>
            <form method="POST">
                <label>Display:</label>
                <select name="theme" onchange="this.form.submit()">
                    <option value="light" <?php echo $theme=='light'?'selected':''; ?>>Light</option>
                    <option value="dark" <?php echo $theme=='dark'?'selected':''; ?>>Dark</option>
                </select>
            </form>
        </div>
        
        <div class="stats">
            <div class="stat-card"><h3>Rezervime</h3><p class="price"><?php echo count($myBookings); ?></p></div>
            <div class="stat-card"><h3>Shpenzuar</h3><p class="price">€<?php echo $totalSpent; ?></p></div>
        </div>
        
        <!-- ADMIN PANEL - Te gjitha rezervimet -->
        <?php if(hasRole('admin')): ?>
        <div class="admin-box">
            <h3>Administratori - Të gjitha rezervimet</h3>
            <?php if(empty($allBookings)): ?>
                <p>Nuk ka rezervime ende.</p>
            <?php else: ?>
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th>Përdoruesi</th>
                            <th>Turi</th>
                            <th>Persona</th>
                            <th>Totali</th>
                            <th>Data</th>
                            <th>Statusi</th>
                            <th>Veprimi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($allBookings as $index => $b): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($b['username']); ?></td>
                        <td><?php echo htmlspecialchars($b['tour']); ?></td>
                        <td><?php echo $b['persons']; ?>
                            <?php if($b['persons'] > 5): ?>
                                <span style="color:green; font-size:0.7rem;"> (10% zbritje)</span>
                            <?php endif; ?>
                        </td>
                        <td>€<?php echo $b['total']; ?></td>
                        <td><?php echo $b['date']; ?></td>
                        <td>
                            <span class="status-<?php echo $b['status']; ?>">
                                <?php echo $b['status'] == 'pending' ? 'Në pritje' : ($b['status'] == 'confirmed' ? 'Konfirmuar' : 'Anuluar'); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display:flex; gap:5px;">
                                <input type="hidden" name="booking_index" value="<?php echo $index; ?>">
                                <select name="status">
                                    <option value="pending" <?php echo $b['status']=='pending'?'selected':''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $b['status']=='confirmed'?'selected':''; ?>>Konfirmuar</option>
                                    <option value="cancelled" <?php echo $b['status']=='cancelled'?'selected':''; ?>>Anuluar</option>
                                </select>
                                <button type="submit" name="update_status">Ndrysho</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
    <?php if(hasRole('user')): ?>
<div class="user-box">
    <h3>Rezervimet e mia</h3>
    
    <?php if(empty($myBookings)): ?>
        <p>Nuk keni rezervime. <a href="tours.php">Rezervo një tur</a></p>
    <?php else: ?>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th>Turi</th>
                    <th>Persona</th>
                    <th>Totali</th>
                    <th>Data</th>
                    <th>Statusi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($myBookings as $b): ?>
            <tr>
                <td><?php echo htmlspecialchars($b['tour']); ?></td>
                <td><?php echo $b['persons']; ?></td>
                <td>€<?php echo $b['total']; ?></td>
                <td><?php echo $b['date']; ?></td>
                <td><?php echo $b['status']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

<style>
<?php if($theme == 'dark'): ?>
body { background: #1a1a2e; }
.container { background: #16213e; color: #eee; }
.stat-card, .admin-box, .user-box { background: #0f3460; }
table { background: #0f3460; }
td, th { border-color: #2c5a7a; }
select, button { background: #1a1a2e; color: #eee; border-color: #2c5a7a; }
.status-pending { color: #ffaa00; }
.status-confirmed { color: #00ff00; }
.status-cancelled { color: #ff4444; }
<?php else: ?>
.status-pending { color: #ff6600; }
.status-confirmed { color: #008000; }
.status-cancelled { color: #ff0000; }
<?php endif; ?>
</style>
</body>
</html>