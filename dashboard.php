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

//Merr rezervimet nga DB 
global $conn;

// Rezervimet e user
$myBookings  = [];
$allBookings = [];
$totalSpent  = 0;

if (hasRole('user')) {
    $stmt = $conn->prepare(
        "SELECT b.*, t.name as tour_name
         FROM bookings b
         JOIN tours t ON b.tour_id = t.id
         JOIN users u ON b.user_id = u.id
         WHERE u.username = ?
         ORDER BY b.total DESC"
    );
    $stmt->bind_param('s', $_SESSION['user']['username']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $myBookings[] = $row;
        $totalSpent  += $row['total'];
    }
    $stmt->close();
}

if (hasRole('admin')) {
    $res = $conn->query(
        "SELECT b.*, u.username, u.name as user_name, t.name as tour_name
         FROM bookings b
         JOIN users u ON b.user_id  = u.id
         JOIN tours t ON b.tour_id  = t.id
         ORDER BY b.booking_date DESC"
    );
    while ($row = $res->fetch_assoc()) $allBookings[] = $row;
    // Totali i shpenzuar
    $totalSpent = array_sum(array_column($allBookings, 'total'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
                <li><a href="admin_tours.php">Edit Tours</a></li>
                <li><a href="admin_users.php">Admin</a></li>
            <?php endif; ?>
            <li><a href="contact.php">Kontakti</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>
    <main>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Dashboard– <?php echo htmlspecialchars($_SESSION['user']['name']); ?></h2>
            <form method="POST">
                <label>Display:</label>
                <select name="theme" onchange="this.form.submit()">
                    <option value="light" <?php echo $theme=='light'?'selected':''; ?>>Light</option>
                    <option value="dark" <?php echo $theme=='dark'?'selected':''; ?>>Dark</option>
                </select>
            </form>
        </div>
        
        <div class="stats">
            <div class="stat-card"><h3>Rezervime</h3><p class="price">
                <?php echo hasRole('admin') ? count($allBookings) : count($myBookings); ?>
                </p>
            </div>
            <div class="stat-card">
                <h3><?php echo hasRole('admin') ? 'Total i gjithë' : 'Shpenzuar'; ?></h3>
                <p class="price">€<?php echo number_format($totalSpent, 2); ?></p>
            </div>
        </div>

        <div id="status-message" style="display:none; margin:.5rem 0;"></div>
        
        <!-- ADMIN PANEL - Te gjitha rezervimet -->
        <?php if(hasRole('admin')): ?>
        <div class="admin-box">
            <h3>Admin - Të gjitha rezervimet</h3>
            <?php if(empty($allBookings)): ?>
                <p>Nuk ka rezervime ende.</p>
            <?php else: ?>
                <table>
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
                    <?php foreach($allBookings as $b): ?>
                    <tr id="row-<?php echo $b['id']; ?>">
                        <td><?php echo htmlspecialchars($b['username']); ?></td>
                        <td><?php echo htmlspecialchars($b['tour_name']); ?></td>
                        <td>
                            <?php echo $b['persons']; ?>
                            <?php if($b['persons'] > 5): ?>
                                <small style="color:green;"> (10% zbritje)</small>
                            <?php endif; ?>
                        </td>
                        <td>€<?php echo number_format($b['total'],2); ?></td>
                        <td><?php echo $b['booking_date']; ?></td>
                        <td>
                            <span class="status-<?php echo $b['status']; ?>" id="status-<?php echo $b['id']; ?>">
                                <?php 
                                echo match($b['status']) {
                                    'confirmed' => 'Konfirmuar',
                                    'cancelled' => 'Anuluar',
                                    default     => 'Në pritje'
                                };
                                ?>
                            </span>
                        </td>
                        <td>
                            <!-- AJAX – ndrysho status pa refresh -->
                            <select id="sel-<?php echo $b['id']; ?>">
                            <option value="pending"   <?php echo $b['status']=='pending'   ?'selected':''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $b['status']=='confirmed' ?'selected':''; ?>>Konfirmuar</option>
                            <option value="cancelled" <?php echo $b['status']=='cancelled' ?'selected':''; ?>>Anuluar</option>
                        </select>
                        <button onclick="updateStatus(<?php echo $b['id']; ?>)">Ndrysho</button>
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
                <td>€<?php echo number_format($b['total'],2); ?></td>
                <td><?php echo $b['booking_date']; ?></td>
                                    <td>
                        <span class="status-<?php echo $b['status']; ?>">
                            <?php echo match($b['status']) {
                                'confirmed' => 'Konfirmuar',
                                'cancelled' => 'Anuluar',
                                default     => 'Në pritje'
                            }; ?>
                        </span>
                    </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    </div>
  <?php endif; ?>
</main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

<!-- AJAX -->
<script>
function updateStatus(bookingId) {
    const status   = document.getElementById('sel-' + bookingId).value;
    const formData = new FormData();
    formData.append('action',     'update_status');
    formData.append('booking_id', bookingId);
    formData.append('status',     status);

    fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // pa refresh
                const label = document.getElementById('status-label-' + bookingId);
                const map   = { pending:'Në pritje', confirmed:'Konfirmuar', cancelled:'Anuluar' };
                label.textContent = map[status];
                label.className   = 'status-' + status;
                showStatusMsg(data.message, 'success');
            } else {
                showStatusMsg(data.message, 'error');
            }
        })
        .catch(() => showStatusMsg('Gabim rrjeti.', 'error'));
}

function showStatusMsg(msg, type) {
    const div = document.getElementById('status-message');
    div.className     = type === 'success' ? 'success' : 'error';
    div.textContent   = msg;
    div.style.display = 'block';
    setTimeout(() => { div.style.display = 'none'; }, 3000);
}
</script>

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
.status-pending { color: #ff6600; font-weight: bold; }
.status-confirmed { color: #008000; font-weight: bold; }
.status-cancelled { color: #ff0000; font-weight: bold; }
<?php endif; ?>
</style>
</body>
</html>