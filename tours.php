//tours

<?php
include 'config.php';
include 'functions.php';
require_once 'tour.php';

// Merr turet nga DB
global $conn;
$result = $conn->query("SELECT * FROM tours ORDER BY id ASC");
$tours  = [];
while ($row = $result->fetch_assoc()) $tours[] = $row;

$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Turet - Tour Guide Prishtina</title>
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
                <li><a href="logout.php" class="logout">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
            <li><a href="contact.php">Kontakti</a></li>
        </ul>
    </nav>
    <main>
        <h2>Turet në Prishtinë</h2>
        <div id="booking-message" style="display:none; margin:.5rem 0;"></div>
         <div class="tours">
            <?php foreach ($tours as $tour):
                $tourObj = new Tour($tour['name'], $tour['price'], $tour['hours'], $tour['spots']);
            ?>
            <div class="tour-card" id="tour-card-<?php echo $tour['id']; ?>">

                <?php if (!empty($tour['image']) && file_exists('uploads/tours/' . $tour['image'])): ?>
                    <img src="uploads/tours/<?php echo htmlspecialchars($tour['image']); ?>"
                         alt="<?php echo htmlspecialchars($tourObj->getName()); ?>"
                         style="width:100%;height:130px;object-fit:cover;border-radius:6px;margin-bottom:.5rem;">
                <?php endif; ?>

                <h3><?php echo htmlspecialchars($tourObj->getName()); ?></h3>
                <p><?php echo $tourObj->getHours(); ?> orë</p>
                <p class="price">€<?php echo $tourObj->getPrice(); ?> / person</p>
                <p>Vende: <span id="spots-<?php echo $tour['id']; ?>"><?php echo $tourObj->getSpots(); ?></span></p>

                <?php if (!empty($tour['description'])): ?>
                    <p style="font-size:.82rem;color:#555;margin-top:.3rem;">
                        <?php echo htmlspecialchars($tour['description']); ?>
                    </p>
                <?php endif; ?>

                <?php if (isLogged() && hasRole('user')): ?>
                    //Forma rezervimit – dorezohet me AJAX 
                    <div style="margin-top:1rem;">
                        <label style="font-size:.85rem;">Numri i personave:</label>
                        <input type="number" id="persons-<?php echo $tour['id']; ?>"
                               min="1" max="<?php echo $tourObj->getSpots(); ?>"
                               value="1" style="width:70px; display:inline-block;">
                        <button onclick="bookTour(<?php echo $tour['id']; ?>)"
                                id="btn-<?php echo $tour['id']; ?>"
                                <?php echo $tourObj->getSpots() == 0 ? 'disabled' : ''; ?>>
                            <?php echo $tourObj->getSpots() > 0 ? 'Rezervo' : 'I plotë'; ?>
                        </button>
                    </div>
                    <?php if ($tourObj->getSpots() > 5): ?>
                        <p style="font-size:.7rem;color:green;margin-top:.3rem;">
                            Mbi 5 persona → 10% zbritje!
                        </p>
                    <?php endif; ?>

                <?php elseif (!isLogged()): ?>
                    <a href="login.php" class="btn"
                       style="display:inline-block;margin-top:1rem;">Kyçu për të rezervuar</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

//AJAX Script per rezervim
<script>
function bookTour(tourId) {
    const persons = document.getElementById('persons-' + tourId).value;
    const btn     = document.getElementById('btn-' + tourId);

    if (!persons || persons < 1) {
        showBookingMsg('Shëno numrin e personave!', 'error');
        return;
    }

    btn.disabled    = true;
    btn.textContent = '...';

    const formData = new FormData();
    formData.append('action',   'book_tour');
    formData.append('tour_id',  tourId);
    formData.append('persons',  persons);

    fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showBookingMsg(data.message, 'success');
                //  pa refresh
                const spotsEl = document.getElementById('spots-' + tourId);
                if (spotsEl) spotsEl.textContent = data.new_spots;
                if (data.new_spots <= 0) {
                    btn.textContent = 'I plotë';
                } else {
                    btn.disabled    = false;
                    btn.textContent = 'Rezervo';
                }
            } else {
                showBookingMsg(data.message, 'error');
                btn.disabled    = false;
                btn.textContent = 'Rezervo';
            }
        })
        .catch(() => {
            showBookingMsg('Gabim rrjeti. Provo përsëri.', 'error');
            btn.disabled    = false;
            btn.textContent = 'Rezervo';
        });
}

function showBookingMsg(msg, type) {
    const div = document.getElementById('booking-message');
    div.className     = type === 'success' ? 'success' : 'error';
    div.textContent   = msg;
    div.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { div.style.display = 'none'; }, 5000);
}
</script>

<style>
<?php if($theme == 'dark'): ?>
body { background: #1a1a2e; }
.container { background: #16213e; color: #eee; }
.tour-card { background: #0f3460; }
input, button { background: #1a1a2e; color: #eee; border-color: #2c5a7a; }
<?php endif; ?>
</style>
</body>
</html>