<?php
$page_title = "Home";
include 'config.php';

$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Guide Prishtina</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Weather Widget */
        .weather-widget {
            background: linear-gradient(135deg, #1e3a5f, #2f5d8a);
            color: #fff; border-radius: 12px; padding: 1.2rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;
        }
        .weather-left  { display: flex; align-items: center; gap: 1rem; }
        .weather-icon  { font-size: 3rem; line-height: 1; }
        .weather-temp  { font-size: 2.2rem; font-weight: bold; }
        .weather-city  { font-size: 1rem; opacity: .85; }
        .weather-desc  { font-size: .9rem; opacity: .8; text-transform: capitalize; }
        .weather-right { font-size: .85rem; opacity: .9; line-height: 1.8; }
        .weather-right span { font-weight: bold; color: #7dd3fc; }
        .weather-loading { opacity: .7; font-style: italic; }
        .weather-error  { background: #7f1d1d; color: #fca5a5; border-radius:8px;
                          padding:.5rem 1rem; font-size:.85rem; margin-bottom:1rem; }
        .cat-grid { display:grid; grid-template-columns:repeat(3,1fr);
                    gap:1rem; margin-top:1.5rem; }
        .cat-card { background:#f9f9f9; padding:20px; border-radius:10px; text-align:center; }
        .cat-card img { width:100%; height:150px; object-fit:cover;
                        border-radius:8px; margin-top:10px; }
        <?php if($theme === 'dark'): ?>
        body { background:#1a1a2e; }
        .container { background:#16213e; color:#eee; }
        .cat-card { background:#0f3460; color:#eee; }
        .weather-widget { background:linear-gradient(135deg,#0f2544,#1e3a5f); }
        <?php endif; ?>
    </style>
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
        <div id="weather-container">
            <div class="weather-widget">
                <div class="weather-left">
                    <div class="weather-icon" id="w-icon"></div>
                    <div>
                        <div class="weather-city">Prishtinë, Kosovë</div>
                        <div class="weather-temp" id="w-temp">–°C</div>
                        <div class="weather-desc" id="w-desc" class="weather-loading">
                            Duke ngarkuar motin...
                        </div>
                    </div>
                </div>
                <div class="weather-right" id="w-details">
                    <div>Lagështia: <span id="w-humidity">–</span>%</div>
                    <div>Era: <span id="w-wind">–</span> km/h</div>
                    <div>Feels: <span id="w-feel">–</span>°C</div>
                    <div style="margin-top:.5rem;font-size:.78rem;opacity:.7;">
                        Burimi: Open-Meteo API
                    </div>
                </div>
            </div>
            <div id="weather-error" class="weather-error" style="display:none;"></div>
        </div>

        <div style="background: #0d317e; color: #f2d6d6; padding: 2rem; text-align: center; border-radius: 10px;">       
            <h1>Mirë se vini në Prishtinë!</h1>
            <p>Eksploroni kryeqytetin me guidat tona profesionale</p>
            <a href="tours.php" class="btn" style="display: inline-block; margin-top: 1rem;">Shiko Turet</a>
        </div>

        <!-- KATEGORITE -->
        <div class="cat-grid">
            <div class="cat-card">
                <h3>Ture Historike</h3>
                <p>Eksploro monumentet</p>
                <img src="foto/newborn.jpg" alt="Historike" onerror="this.style.display='none'">
            </div>
            <div class="cat-card">
                <h3>Gastronomi</h3>
                <p>Shijo ushqimet tradicionale</p>
                <img src="foto/gastronomia.jpg" alt="Gastronomi" onerror="this.style.display='none'">
            </div>
            <div class="cat-card">
                <h3>Night Life</h3>
                <p>Përjeto Prishtinën ndryshe</p>
                <img src="foto/nightlife.jpg" alt="Night Life" onerror="this.style.display='none'">
            </div>
        </div>
    </main>
    <footer>&copy; 2026 Tour Guide Prishtina</footer>
</div>

<!--  JAVASCRIPT -->
<script>
async function fetchWeather() {
    const API_URL =
        'https://api.open-meteo.com/v1/forecast?' +
        'latitude=42.6629&longitude=21.1655' +
        '&current=temperature_2m,apparent_temperature,relative_humidity_2m,' +
        'wind_speed_10m,weather_code' +
        '&temperature_unit=celsius&wind_speed_unit=kmh&timezone=Europe%2FBelgrade';

    try {
        const response = await fetch(API_URL);

        if (!response.ok) {
            throw new Error('API nuk u përgjigj (' + response.status + ')');
        }

        const data = await response.json();
        const cur  = data.current;

        // Ploteson DOM
        document.getElementById('w-temp').textContent    = Math.round(cur.temperature_2m) + '°C';
        document.getElementById('w-feel').textContent    = Math.round(cur.apparent_temperature);
        document.getElementById('w-humidity').textContent= cur.relative_humidity_2m;
        document.getElementById('w-wind').textContent    = Math.round(cur.wind_speed_10m);

        // Konverto WMO Weather Code ikone + pershkrim
        const weather = wmoToInfo(cur.weather_code);
        document.getElementById('w-icon').textContent = weather.icon;
        document.getElementById('w-desc').textContent = weather.desc;

        // advice sipas motit
        const tip = document.createElement('div');
        tip.style.cssText = 'font-size:.8rem;opacity:.75;margin-top:.3rem;';
        tip.textContent   = weather.tourTip;
        document.getElementById('w-desc').after(tip);

    } catch (err) {
        const errEl = document.getElementById('weather-error');
        errEl.textContent = 'Moti nuk u ngarkua: ' + err.message;
        errEl.style.display = 'block';
        document.getElementById('w-icon').textContent = '?';
        document.getElementById('w-desc').textContent = 'Moti i panjohur';
        console.warn('Open-Meteo error:', err);
    }
}

function wmoToInfo(code) {
    const map = {
        0:  { icon: '☀️',  desc: 'Kthjellët',           tourTip: '🟢 Kohë perfekte për ture!' },
        1:  { icon: '🌤️',  desc: 'Kryesisht kthjellët', tourTip: '🟢 Kohë e mirë për ture.' },
        2:  { icon: '⛅',  desc: 'Pjesërisht vranët',   tourTip: '🟡 Ture të shkurtra rekomandohen.' },
        3:  { icon: '☁️',  desc: 'Vranët',              tourTip: '🟡 Merr xhaketë.' },
        45: { icon: '🌫️',  desc: 'Mjegull',             tourTip: '🔴 Kujdes në ture natyrore.' },
        48: { icon: '🌫️',  desc: 'Mjegull me ngricë',   tourTip: '🔴 Kujdes - rrugët janë të rrëshqitshme.' },
        51: { icon: '🌦️',  desc: 'Shira i lehtë',       tourTip: '🟡 Merr çadër.' },
        53: { icon: '🌧️',  desc: 'Shira mesatar',       tourTip: '🟡 Merr çadër dhe pallto.' },
        55: { icon: '🌧️',  desc: 'Shira i dendur',      tourTip: '🔴 Turet e brendshme rekomandohen.' },
        61: { icon: '🌧️',  desc: 'Shi i lehtë',         tourTip: '🟡 Merr çadër.' },
        63: { icon: '🌧️',  desc: 'Shi mesatar',         tourTip: '🟡 Merr çadër dhe pallto.' },
        65: { icon: '🌧️',  desc: 'Shi i fortë',         tourTip: '🔴 Turet e brendshme rekomandohen.' },
        71: { icon: '❄️',  desc: 'Borë e lehtë',        tourTip: '⚪ Prishtina nën borë – pamje mrekulluese!' },
        73: { icon: '❄️',  desc: 'Borë mesatare',       tourTip: '⚪ Merr rroba të ngrohta.' },
        75: { icon: '❄️',  desc: 'Borë e dendur',       tourTip: '🔴 Kujdes gjatë turit.' },
        80: { icon: '🌦️',  desc: 'Shira të lehtë',      tourTip: '🟡 Merr çadër.' },
        81: { icon: '🌧️',  desc: 'Shira mesatare',      tourTip: '🟡 Merr çadër dhe pallto.' },
        82: { icon: '⛈️',  desc: 'Shira të fortë',      tourTip: '🔴 Turet e brendshme rekomandohen.' },
        95: { icon: '⛈️',  desc: 'Stuhi',               tourTip: '🔴 Qëndro brenda – sigurie!' },
        96: { icon: '⛈️',  desc: 'Stuhi me breshër',    tourTip: '🔴 Qëndro brenda – sigurie!' },
        99: { icon: '⛈️',  desc: 'Stuhi e fortë',       tourTip: '🔴 Qëndro brenda – sigurie!' },
    };
    return map[code] || { icon: '🌡️', desc: 'Gjendje e panjohur', tourTip: '' };
}

// Ekzekuto kur ngarkohet faqja
fetchWeather();
</script>

<style>
<?php if($theme == 'dark'): ?>
body { background: #1a1a2e; }
.container { background: #16213e; color: #eee; }
main > div:last-child div { background: #0f3460 !important; color: #eee; }
<?php endif; ?>
</style>
</body>
</html>