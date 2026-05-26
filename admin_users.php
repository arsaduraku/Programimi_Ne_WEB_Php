<?php
require_once 'db_connect.php';
include 'config.php';
include 'functions.php';

requireAdmin();

$theme = $_COOKIE['theme'] ?? 'light';

// READ 
$result = $conn->query(
    "SELECT u.*, COUNT(b.id) as booking_count
     FROM users u
     LEFT JOIN bookings b ON u.id = b.user_id
     GROUP BY u.id
     ORDER BY u.created_at DESC"
);
$users = [];
while ($row = $result->fetch_assoc()) $users[] = $row;

// Statistika
$totalUsers  = count($users);
$adminCount  = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
$userCount   = $totalUsers - $adminCount;
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Admin – Menaxho Përdoruesit</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .role-badge {
            display: inline-block; padding: 2px 10px;
            border-radius: 12px; font-size: .78rem; font-weight: bold;
        }
        .role-admin { background: #fef3c7; color: #92400e; }
        .role-user  { background: #dbeafe; color: #1e40af; }

        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.55); z-index: 999;
            justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #fff; border-radius: 12px;
            padding: 2rem; width: 90%; max-width: 460px;
            box-shadow: 0 10px 40px rgba(0,0,0,.25);
            position: relative;
        }
        .modal-close {
            position: absolute; top: 12px; right: 16px;
            font-size: 1.4rem; cursor: pointer;
            background: none; border: none; color: #555;
        }
        .modal-box label { font-size:.85rem; font-weight:bold; display:block; margin-top:.6rem; }
        .modal-box input, .modal-box select { margin-top:.2rem; }
        .modal-box .hint { font-size:.78rem; color:#888; margin-top:.2rem; }
        <?php if($theme === 'dark'): ?>
        body          { background: #1a1a2e; }
        .container    { background: #16213e; color: #eee; }
        .admin-box    { background: #0f3460; }
        .modal-box    { background: #16213e; color: #eee; border:1px solid #2c5a7a; }
        .modal-close  { color: #ccc; }
        table         { background: #0f3460; }
        td, th        { border-color: #2c5a7a; }
        input, select, button { background: #1a1a2e; color: #eee; border-color: #2c5a7a; }
        .role-admin   { background: #451a03; color: #fcd34d; }
        .role-user    { background: #1e3a5f; color: #93c5fd; }
        <?php endif; ?>
    </style>
</head>
<body>
<div class="container">

    <!-- NAV -->
    <nav>
        <div>Tour Guide Prishtina</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="admin_tours.php">Edit Tours</a></li>
            <li><a href="admin_users.php" style="color:#fff;font-weight:bold;">Admin</a></li>
            <li><a href="contact.php">Kontakti</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>

    <main>
        <h2>Menaxho Përdoruesit</h2>
        <div id="msg" style="display:none; margin:.5rem 0;"></div>

        <!-- Statistika -->
        <div class="stats" style="margin-bottom:1.5rem;">
            <div class="stat-card">
                <h3>Total Përdorues</h3>
                <p class="price"><?php echo $totalUsers; ?></p>
            </div>
            <div class="stat-card">
                <h3>Admin</h3>
                <p class="price"><?php echo $adminCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>Klientë</h3>
                <p class="price"><?php echo $userCount; ?></p>
            </div>
        </div>

        <div style="margin-bottom:1rem;">
            <a href="login.php" class="btn" style="background:#2f5d8a;">
                ➕ Regjistro Përdorues të Ri
            </a>
        </div>

        <!-- READ -->
        <div class="admin-box" style="overflow-x:auto;">
            <?php if (empty($users)): ?>
                <p>Nuk ka përdorues.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Emri</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Roli</th>
                        <th>Rezervime</th>
                        <th>Regjistruar</th>
                        <th>Veprimet</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                <?php foreach ($users as $u): ?>
                <tr id="urow-<?php echo $u['id']; ?>">
                    <td><?php echo $u['id']; ?></td>
                    <td id="uname-<?php echo $u['id']; ?>">
                        <?php echo htmlspecialchars($u['name']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td id="uemail-<?php echo $u['id']; ?>">
                        <?php echo htmlspecialchars($u['email'] ?? '–'); ?>
                    </td>
                    <td id="urole-<?php echo $u['id']; ?>">
                        <span class="role-badge role-<?php echo $u['role']; ?>">
                            <?php echo $u['role'] === 'admin' ? 'Admin' : 'User'; ?>
                        </span>
                    </td>
                    <td style="text-align:center;"><?php echo $u['booking_count']; ?></td>
                    <td><?php echo date('d.m.Y', strtotime($u['created_at'])); ?></td>
                    <td>
                        <div style="display:flex;gap:.3rem;">
                            <!-- EDIT -->
                            <button onclick="openEdit(
                                <?php echo $u['id']; ?>,
                                '<?php echo addslashes(htmlspecialchars($u['name'])); ?>',
                                '<?php echo addslashes(htmlspecialchars($u['email'] ?? '')); ?>',
                                '<?php echo $u['role']; ?>'
                            )" style="background:#2f5d8a;padding:.3rem .7rem;font-size:.8rem;">
                                Edit
                            </button>
                            <!-- DELETE-->
                            <?php if ($u['username'] !== $_SESSION['user']['username']): ?>
                            <button onclick="deleteUser(<?php echo $u['id']; ?>)"
                                    style="background:#8b0000;padding:.3rem .7rem;font-size:.8rem;">
                                Delete
                            </button>
                            <?php else: ?>
                            <button disabled title="Nuk mund të fshish llogarinë tënde"
                                    style="background:#666;padding:.3rem .7rem;font-size:.8rem;">
                                ADMIN
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>

    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

<!-- Update AJAX Modal -->
<div class="modal-overlay" id="edit-modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeEdit()">✕</button>
        <h3>Ndrysho Përdoruesin</h3>
        <input type="hidden" id="e-id">

        <label>Emri i plotë *</label>
        <input type="text" id="e-name" placeholder="Emri i plotë" required>

        <label>Email *</label>
        <input type="email" id="e-email" placeholder="email@example.com" required>

        <label>Roli *</label>
        <select id="e-role" style="width:100%;padding:.5rem;margin:.2rem 0;
                border:1px solid #d1d5db;border-radius:5px;">
            <option value="user">User (Klient)</option>
            <option value="admin">Admin</option>
        </select>

        <label>Fjalëkalim i ri</label>
        <input type="password" id="e-pass" placeholder="Min. 6 karaktere (opsional)">
        <p class="hint">Nëse nuk dëshiron ta ndryshosh fjalëkalimin, lëre këtë fushë bosh.</p>

        <div style="display:flex;gap:.5rem;margin-top:1rem;">
            <button onclick="saveEdit()" style="flex:1;">Ruaj</button>
            <button onclick="closeEdit()" style="flex:1;background:#888;">Anulo</button>
        </div>
        <span id="edit-spin" style="display:none;">Duke ruajtur...</span>
    </div>
</div>

<!-- JAVASCRIPT – AJAX -->
<script>
// UPDATE hap
function openEdit(id, name, email, role) {
    document.getElementById('e-id').value    = id;
    document.getElementById('e-name').value  = name;
    document.getElementById('e-email').value = email;
    document.getElementById('e-role').value  = role;
    document.getElementById('e-pass').value  = '';
    document.getElementById('edit-modal').classList.add('active');
}
function closeEdit() {
    document.getElementById('edit-modal').classList.remove('active');
}

//  UPDATE: Ruaj 
function saveEdit() {
    const id    = document.getElementById('e-id').value;
    const name  = document.getElementById('e-name').value.trim();
    const email = document.getElementById('e-email').value.trim();
    const role  = document.getElementById('e-role').value;
    const pass  = document.getElementById('e-pass').value;

    if (!name || !email) {
        showMsg('Plotëso emrin dhe email-in!', 'error');
        return;
    }

    const spin = document.getElementById('edit-spin');
    spin.style.display = 'inline';

    const fd = new FormData();
    fd.append('action',   'edit_user');
    fd.append('id',       id);
    fd.append('name',     name);
    fd.append('email',    email);
    fd.append('role',     role);
    fd.append('password', pass);

    fetch('ajax_handler.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // pa refresh
                document.getElementById('uname-' + id).textContent  = name;
                document.getElementById('uemail-' + id).textContent = email;
                const roleEl = document.getElementById('urole-' + id);
                roleEl.innerHTML = `<span class="role-badge role-${role}">
                    ${role === 'admin' ? ' Admin' : 'User'}</span>`;
                showMsg(data.message, 'success');
                closeEdit();
            } else {
                showMsg(data.message, 'error');
            }
        })
        .catch(() => showMsg('Gabim rrjeti.', 'error'))
        .finally(() => spin.style.display = 'none');
}

// DELETE: Fshi User 
function deleteUser(id) {
    if (!confirm('A jeni i sigurt?\nTë gjitha rezervimet e këtij përdoruesi do të fshihen gjithashtu!'))
        return;

    const fd = new FormData();
    fd.append('action', 'delete_user');
    fd.append('id', id);

    fetch('ajax_handler.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('urow-' + id);
                if (row) {
                    row.style.transition = 'opacity .4s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 420);
                }
                showMsg(data.message, 'success');
            } else {
                showMsg(data.message, 'error');
            }
        })
        .catch(() => showMsg('Gabim rrjeti.', 'error'));
}

// shfaq mesazh 
function showMsg(msg, type) {
    const div = document.getElementById('msg');
    div.className   = type === 'success' ? 'success' : 'error';
    div.textContent = msg;
    div.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => div.style.display = 'none', 4500);
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEdit(); });
</script>

</body>
</html>