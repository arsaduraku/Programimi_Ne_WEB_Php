//tours

<?php
include 'config.php';
include 'functions.php';
require_once 'tour.php';

$message = '';

// Rezervimi
if(isset($_POST['book'])) {

    if(!isLogged()) {
        $message = "<div class='error'>Duhet të kyçeni për të rezervuar!</div>";
    }

    elseif(!hasRole('user')) {
        $message = "<div class='error'>Vetëm klientët mund të rezervojnë ture. Ju jeni administrator.</div>";
    }

    else {

        $tourId = (int)$_POST['tour_id'];
        $persons = (int)$_POST['persons'];

        foreach($tours as $t) {
            if($t['id'] == $tourId) {

                if($persons <= $t['spots']) {

                    $total = $persons * $t['price'];

                    addBooking(
                        $_SESSION['user']['username'],
                        $t['name'],
                        $persons,
                        $total
                    );

                    // Përditëso vendet e lira
                    foreach($_SESSION['tours'] as $key => $tour) {
                        if($tour['id'] == $tourId) {
                            $_SESSION['tours'][$key]['spots'] -= $persons;
                            $tours = $_SESSION['tours'];
                            break;
                        }
                    }

                    $message = "<div class='success'>
                        Rezervuat {$t['name']} për $persons persona! Totali: €$total";

                    if($persons > 5) {
                        $message .= " (10% zbritje e aplikuar!)";
                    }

                    $message .= "</div>";

                } else {
                    $message = "<div class='error'>
                        Nuk ka vende të mjaftueshme! Vendet e lira: {$t['spots']}
                    </div>";
                }

                break;
            }
        }
    }
}

$tours = $_SESSION['tours'];
$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Turet - Tour Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <div>Tour Guide Prishtina</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <?php if(hasRole('user')): ?>
            <li><a href="tours.php">Tours</a></li>
            <?php endif; ?>
            <?php if(isLogged() && hasRole('user')): ?>
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
        <h2>Turet në Prishtinë</h2>
        <?php echo $message; ?>
        
        <div class="tours">
           <?php foreach($tours as $tour): 
           $tourObj = new Tour($tour['name'], $tour['price'], $tour['hours'], $tour['spots']);
           ?>
        <div class="tour-card">
        <h3><?php echo htmlspecialchars($tourObj->getName()); ?></h3>
        <p><?php echo $tourObj->getHours(); ?> orë</p>
        <p class="price">€<?php echo $tourObj->getPrice(); ?> / person</p>
        <p>Vende: <?php echo $tourObj->getSpots(); ?></p>
                
                <?php if(isLogged()): ?>
                    <form method="POST" style="margin-top: 1rem;">
                        <input type="hidden" name="tour_id" value="<?php echo $tour['id']; ?>">
                        <label>Numri i personave:</label>
                        <input type="number" name="persons" min="1" max="<?php echo $tour['spots']; ?>" value="1" style="width: 80px;">
                        <button type="submit" name="book">Rezervo</button>
                    </form>
                    <?php if($tour['spots'] > 5): ?>
                        <p style="font-size:0.7rem; color:green; margin-top:5px;">Rezervo më shumë se 5 persona dhe fito 10% zbritje!</p>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn" style="display: inline-block; margin-top: 1rem;">Kyqu për të rezervuar</a>
                <?php endif; ?>
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
.tour-card { background: #0f3460; }
input, button { background: #1a1a2e; color: #eee; border-color: #2c5a7a; }
<?php endif; ?>
</style>
</body>
</html>