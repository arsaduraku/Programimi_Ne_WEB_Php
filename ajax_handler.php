<?php
require_once 'db_connect.php';   // $conn
require_once 'config.php';       // session_start(), isLogged(), hasRole()

header('Content-Type: application/json; charset=utf-8');

// Helpers
function ajaxError($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}
function needLogin() {
    if (!isLogged()) ajaxError('Duhet të jeni të kyçur!', 401);
}
function needAdmin() {
    if (!isLogged() || !hasRole('admin')) ajaxError('Nuk keni leje!', 403);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {

    switch ($action) {
        //CRUD per toure
        // CREATE) 
        case 'add_tour':
            needAdmin();

            $name  = trim($_POST['name']        ?? '');
            $hours = (float)($_POST['hours']    ?? 0);
            $price = (float)($_POST['price']    ?? 0);
            $spots = (int)($_POST['spots']      ?? 0);
            $desc  = trim($_POST['description'] ?? '');

            if (empty($name))  ajaxError('Emri është i detyrueshëm!');
            if ($hours <= 0)   ajaxError('Orët duhet të jenë pozitive!');
            if ($price <= 0)   ajaxError('Çmimi duhet të jetë pozitiv!');
            if ($spots <= 0)   ajaxError('Vendet duhet të jenë pozitive!');

            // Upload foto
            $imageName = null;
            if (!empty($_FILES['image']['name'])) {
                $allowed = ['jpg','jpeg','png','gif','webp'];
                $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed))
                    ajaxError('Format i pavlefshëm! Lejo: jpg, png, gif, webp');
                if ($_FILES['image']['size'] > 2 * 1024 * 1024)
                    ajaxError('Imazhi shumë i madh! Max: 2MB');
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK)
                    ajaxError('Gabim gjatë ngarkimit!');

                $dir = 'uploads/tours/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $imageName = uniqid('tour_', true) . '.' . $ext;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir . $imageName))
                    ajaxError('Ngarkimi dështoi – kontrollo lejet e folderit uploads/');
            }

            $stmt = $conn->prepare(
                "INSERT INTO tours (name, hours, price, spots, description, image)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sdidss', $name, $hours, $price, $spots, $desc, $imageName);
            if (!$stmt->execute()) throw new Exception($conn->error);
            $newId = $conn->insert_id;
            $stmt->close();

            echo json_encode([
                'success' => true,
                'message' => "Turi '" . htmlspecialchars($name) . "' u shtua!",
                'tour'    => [
                    'id'    => $newId,
                    'name'  => htmlspecialchars($name),
                    'hours' => $hours, 'price' => $price, 'spots' => $spots,
                    'description' => htmlspecialchars($desc),
                    'image' => $imageName ? 'uploads/tours/' . $imageName : null
                ]
            ]);
            break;

        // UPDATE)
        case 'edit_tour':
            needAdmin();

            $id    = (int)($_POST['id']           ?? 0);
            $name  = trim($_POST['name']          ?? '');
            $hours = (float)($_POST['hours']      ?? 0);
            $price = (float)($_POST['price']      ?? 0);
            $spots = (int)($_POST['spots']        ?? 0);
            $desc  = trim($_POST['description']   ?? '');

            if ($id <= 0)      ajaxError('ID e pavlefshme!');
            if (empty($name))  ajaxError('Emri është i detyrueshëm!');
            if ($hours <= 0)   ajaxError('Orët duhet të jenë pozitive!');
            if ($price <= 0)   ajaxError('Çmimi duhet të jetë pozitiv!');
            if ($spots < 0)    ajaxError('Vendet nuk mund të jenë negative!');

            $stmt = $conn->prepare(
                "UPDATE tours SET name=?, hours=?, price=?, spots=?, description=?
                 WHERE id=?"
            );
            $stmt->bind_param('sdidsi', $name, $hours, $price, $spots, $desc, $id);
            if (!$stmt->execute()) throw new Exception($conn->error);
            $stmt->close();

            echo json_encode([
                'success' => true,
                'message' => "Turi u ndryshua me sukses!",
                'tour'    => [
                    'id' => $id, 'name' => htmlspecialchars($name),
                    'hours' => $hours, 'price' => $price,
                    'spots' => $spots, 'description' => htmlspecialchars($desc)
                ]
            ]);
            break;

        // DELETE
        case 'delete_tour':
            needAdmin();

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) ajaxError('ID e pavlefshme!');

            $stmt = $conn->prepare("DELETE FROM tours WHERE id=?");
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) throw new Exception($conn->error);
            $stmt->close();

            echo json_encode(['success' => true, 'message' => 'Turi u fshi!']);
            break;

        // READ
        case 'get_tour':
            needAdmin();

            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) ajaxError('ID e pavlefshme!');

            $stmt = $conn->prepare("SELECT * FROM tours WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $tour = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$tour) ajaxError('Turi nuk u gjet!');
            echo json_encode(['success' => true, 'tour' => $tour]);
            break;

        //CRUD per bookings
        // CREATE
        case 'book_tour':
            needLogin();
            if (hasRole('admin')) ajaxError('Adminët nuk mund të rezervojnë ture!');

            $tourId  = (int)($_POST['tour_id'] ?? 0);
            $persons = (int)($_POST['persons'] ?? 0);

            if ($tourId <= 0 || $persons <= 0) ajaxError('Të dhëna të pavlefshme!');

            $stmt = $conn->prepare("SELECT * FROM tours WHERE id=?");
            $stmt->bind_param('i', $tourId);
            $stmt->execute();
            $tour = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$tour)                        ajaxError('Turi nuk u gjet!');
            if ($persons > $tour['spots'])     ajaxError("Vende të lira: {$tour['spots']}");

            $total    = round($persons * $tour['price'], 2);
            $discount = false;
            if ($persons > 5) { $total = round($total * 0.9, 2); $discount = true; }

            // Gjej user_id
            $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
            $stmt->bind_param('s', $_SESSION['user']['username']);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$user) ajaxError('Përdoruesi nuk u gjet!');

            $stmt = $conn->prepare(
                "INSERT INTO bookings (user_id, tour_id, persons, total) VALUES (?,?,?,?)"
            );
            $stmt->bind_param('iiid', $user['id'], $tourId, $persons, $total);
            if (!$stmt->execute()) throw new Exception($conn->error);
            $stmt->close();

            $stmt2 = $conn->prepare("UPDATE tours SET spots = spots - ? WHERE id=?");
            $stmt2->bind_param('ii', $persons, $tourId);
            $stmt2->execute();
            $stmt2->close();

            $msg = "Rezervuat '" . htmlspecialchars($tour['name']) . "' për $persons persona! Totali: €$total";
            if ($discount) $msg .= " (10% zbritje aplikuar!)";

            echo json_encode([
                'success'   => true,
                'message'   => $msg,
                'new_spots' => $tour['spots'] - $persons
            ]);
            break;

        // UPDATE
        case 'update_status':
            needAdmin();

            $bookingId = (int)($_POST['booking_id'] ?? 0);
            $status    = $_POST['status'] ?? '';

            if (!in_array($status, ['pending','confirmed','cancelled']))
                ajaxError('Status i pavlefshëm!');
            if ($bookingId <= 0) ajaxError('ID e pavlefshme!');

            $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
            $stmt->bind_param('si', $status, $bookingId);
            if (!$stmt->execute()) throw new Exception($conn->error);
            $stmt->close();

            echo json_encode(['success' => true, 'message' => 'Statusi u ndryshua!']);
            break;

        // DELETE
        case 'delete_booking':
            needAdmin();

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) ajaxError('ID e pavlefshme!');

            $stmt = $conn->prepare("DELETE FROM bookings WHERE id=?");
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) throw new Exception($conn->error);
            $stmt->close();

            echo json_encode(['success' => true, 'message' => 'Rezervimi u fshi!']);
            break;

        //CRUD per users vetem admin mundet
        // UPDATE
        case 'edit_user':
            needAdmin();

            $id    = (int)($_POST['id']    ?? 0);
            $name  = trim($_POST['name']   ?? '');
            $email = trim($_POST['email']  ?? '');
            $role  = trim($_POST['role']   ?? '');
            $pass  = $_POST['password']    ?? '';

            if ($id <= 0)           ajaxError('ID e pavlefshme!');
            if (empty($name))       ajaxError('Emri është i detyrueshëm!');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) ajaxError('Email i pavlefshëm!');
            if (!in_array($role, ['admin','user']))         ajaxError('Roli i pavlefshëm!');

            if (!empty($pass)) {
                if (strlen($pass) < 6) ajaxError('Fjalëkalimi duhet ≥ 6 karaktere!');
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                $stmt   = $conn->prepare(
                    "UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?"
                );
                $stmt->bind_param('ssssi', $name, $email, $role, $hashed, $id);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE users SET name=?, email=?, role=? WHERE id=?"
                );
                $stmt->bind_param('sssi', $name, $email, $role, $id);
            }

            if (!$stmt->execute()) throw new Exception($conn->error);
            $stmt->close();

            echo json_encode(['success' => true, 'message' => 'Përdoruesi u ndryshua!']);
            break;

        // DELETE 
        case 'delete_user':
            needAdmin();

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) ajaxError('ID e pavlefshme!');

            // Mos fshi veten
            $stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row && $row['username'] === $_SESSION['user']['username'])
                ajaxError('Nuk mund të fshish llogarinë tuaj!');

            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) throw new Exception($conn->error);
            $stmt->close();

            echo json_encode(['success' => true, 'message' => 'Përdoruesi u fshi!']);
            break;

        default:
            ajaxError('Veprim i panjohur: ' . htmlspecialchars($action));
    }

} catch (Exception $e) {
    $logLine = date('Y-m-d H:i:s') . " [AJAX:$action] " . $e->getMessage() . "\n";
    @file_put_contents('errors.log', $logLine, FILE_APPEND | LOCK_EX);
    ajaxError('Gabim i brendshëm. Shiko errors.log për detaje.');
}
?>