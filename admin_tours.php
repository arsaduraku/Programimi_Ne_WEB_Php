<?php
include 'config.php';
include 'functions.php';
require_once 'tour.php';

// Vetem admini ka qasje
requireAdmin();

$theme = $_COOKIE['theme'] ?? 'light';

// Merr turet nga DB
global $conn;
$result = $conn->query("SELECT * FROM tours ORDER BY created_at DESC");
$tours  = [];
while ($row = $result->fetch_assoc()) $tours[] = $row;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Menaxho Tours -  Admin</title>
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
            <li><a href="admin_tours.php">Edit Tours</a></li>
            <li><a href="admin_users.php">Admin</a></li>
            <li><a href="contact.php">Kontakti</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h2>Menaxho Turet</h2>
        <!-- Mesazhet AJAX -->
        <div id="ajax-message" style="display:none; margin:.5rem 0;"></div>

        <div class="admin-box" style="margin-bottom:2rem;">
            <h3>Shto Tur të Ri</h3>
            <form id="add-tour-form" enctype="multipart/form-data">
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem;">
                    <input type="text"   id="t-name"  placeholder="Emri i turit"  required>
                    <input type="number" id="t-hours" step="0.5" placeholder="Orët" required>
                    <input type="number" id="t-price" step="0.5" placeholder="Çmimi (€)" required>
                    <input type="number" id="t-spots" placeholder="Vendet" required>
                </div>
                <textarea id="t-desc" rows="2"
                    placeholder="Përshkrim (opsional)"
                    style="width:100%;margin-top:.5rem;padding:.5rem;border:1px solid #d1d5db;
                           border-radius:5px;font-family:Arial;"></textarea>

                <!-- Upload imazhi -->
                <div style="margin-top:.5rem;">
                    <label style="font-weight:bold;">Imazhi i turit (opsional, max 2MB):</label><br>
                    <input type="file" id="t-image" accept="image/*"
                           style="margin-top:.3rem;padding:.3rem;">
                    <img id="t-preview" src="" alt=""
                         style="display:none;max-height:80px;border-radius:5px;margin-left:10px;">
                </div>

                <button type="submit" id="add-btn" style="margin-top:1rem;">
                    ➕ Shto Tur
                </button>
                <span id="add-spinner" style="display:none; margin-left:10px;">Duke ruajtur...</span>
            </form>
        </div>

        <!-- Lista e tureve ekzistuese -->
        <h3>Turet Ekzistuese</h3>
        <div class="tours" id="tours-list">
            <?php foreach($tours as $tour):
            // KRIJOJMË OBJEKT TOUR - PA NDRYSHUAR LOGJIKËN E TJETËR
            $tourObj = new Tour($tour['name'], $tour['price'], $tour['hours'], $tour['spots']);
            ?>
            <div class="tour-card" id="card-<?php echo $tour['id']; ?>">
                <?php if (!empty($tour['image']) && file_exists('uploads/tours/' . $tour['image'])): ?>
                    <img src="uploads/tours/<?php echo htmlspecialchars($tour['image']); ?>"
                         alt="<?php echo htmlspecialchars($tourObj->getName()); ?>"
                         style="width:100%;height:120px;object-fit:cover;border-radius:6px;margin-bottom:.5rem;">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($tourObj->getName()); ?></h3>
                <p><?php echo $tourObj->getHours(); ?> orë</p>
                <p class="price">€<?php echo $tourObj->getPrice(); ?> / person</p>
                <p>Vende: <span id="spots-<?php echo $tour['id']; ?>"><?php echo $tourObj->getSpots(); ?></span></p>
                <?php if (!empty($tour['description'])): ?>
                    <p style="font-size:.85rem;color:#666;margin-top:.3rem;">
                        <?php echo htmlspecialchars($tour['description']); ?>
                    </p>
                <?php endif; ?>
                <button onclick="deleteTour(<?php echo $tour['id']; ?>, this)"
                        style="background:#8b0000;margin-top:.5rem;width:100%;">
                    X Delete
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

//Ajax Script
<script>
// Preview imazh para ngarkimit
document.getElementById('t-image').addEventListener('change', function() {
    const preview = document.getElementById('t-preview');
    const file = this.files[0];
    if (file) {
        preview.src    = URL.createObjectURL(file);
        preview.style.display = 'inline-block';
    } else {
        preview.style.display = 'none';
    }
});

// Shto tur me AJAX (pa refresh faqe)
document.getElementById('add-tour-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Ndalon refresh

    const btn     = document.getElementById('add-btn');
    const spinner = document.getElementById('add-spinner');

    // Merr vlerat
    const name  = document.getElementById('t-name').value.trim();
    const hours = document.getElementById('t-hours').value;
    const price = document.getElementById('t-price').value;
    const spots = document.getElementById('t-spots').value;
    const desc  = document.getElementById('t-desc').value.trim();
    const image = document.getElementById('t-image').files[0];

    if (!name || !hours || !price || !spots) {
        showMessage('Plotëso të gjitha fushat e detyrueshme!', 'error');
        return;
    }

    // FormData mbështet edhe file upload
    const formData = new FormData();
    formData.append('action', 'add_tour');
    formData.append('name',   name);
    formData.append('hours',  hours);
    formData.append('price',  price);
    formData.append('spots',  spots);
    formData.append('description', desc);
    if (image) formData.append('image', image);

    btn.disabled = true;
    spinner.style.display = 'inline';

    fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                addTourCard(data.tour);       // Shto card pa refresh
                clearForm();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(() => showMessage('Gabim rrjeti. Provo përsëri.', 'error'))
        .finally(() => {
            btn.disabled = false;
            spinner.style.display = 'none';
        });
});

//Fshi tur me AJAX 
function deleteTour(id, btn) {
    if (!confirm('A jeni i sigurt që doni të fshini këtë tur?')) return;

    btn.disabled = true;
    btn.textContent = 'Duke fshirë...';

    const formData = new FormData();
    formData.append('action', 'delete_tour');
    formData.append('id', id);

    fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Heq Card nga DOM pa refresh
                const card = document.getElementById('card-' + id);
                if (card) {
                    card.style.transition = 'opacity .4s';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 400);
                }
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
                btn.disabled = false;
                btn.textContent = 'X Delete';
            }
        })
        .catch(() => {
            showMessage('Gabim rrjeti.', 'error');
            btn.disabled = false;
            btn.textContent = 'X Delete';
        });
}

//Helper: shfaq mesazhin
function showMessage(msg, type) {
    const div = document.getElementById('ajax-message');
    div.className = type === 'success' ? 'success' : 'error';
    div.textContent = msg;
    div.style.display = 'block';
    setTimeout(() => { div.style.display = 'none'; }, 4000);
}

// shtim i new card ne DOM 
function addTourCard(tour) {
    const list = document.getElementById('tours-list');
    const card = document.createElement('div');
    card.className = 'tour-card';
    card.id = 'card-' + tour.id;
    card.style.opacity = '0';
    card.innerHTML = `
        ${tour.image ? `<img src="${tour.image}" style="width:100%;height:120px;object-fit:cover;border-radius:6px;margin-bottom:.5rem;">` : ''}
        <h3>${escHtml(tour.name)}</h3>
        <p>${tour.hours} orë</p>
        <p class="price">€${tour.price} / person</p>
        <p>Vende: <span id="spots-${tour.id}">${tour.spots}</span></p>
        <button onclick="deleteTour(${tour.id}, this)"
                style="background:#8b0000;margin-top:.5rem;width:100%;">X Delete</button>
    `;
    list.prepend(card);
    // Animacion hyrje
    setTimeout(() => { card.style.transition = 'opacity .4s'; card.style.opacity = '1'; }, 10);
}

//pastro formën 
function clearForm() {
    ['t-name','t-hours','t-price','t-spots','t-desc'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('t-image').value = '';
    document.getElementById('t-preview').style.display = 'none';
}

// escape HTML (XSS protection në JS) 
function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
              .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>

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