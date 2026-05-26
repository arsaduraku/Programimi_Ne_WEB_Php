<?php
// weather_api.php - Shfaq motin aktual ne Prishtine
function getWeather() {
    $city = "Prishtina";
    $apiKey = " ";
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric&lang=en";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    return null;
}

$weather = getWeather();
?>
<?php if($weather): ?>
<div class="weather-widget" style="background:#f0f4f8; padding:1rem; border-radius:10px; margin-bottom:1rem; text-align:center;">
    <h3>Moti në Prishtinë</h3>
    <p><strong><?php echo $weather['weather'][0]['description']; ?></strong></p>
    <p>Temperatura: <?php echo round($weather['main']['temp']); ?>°C</p>
    <p>Lagështia: <?php echo $weather['main']['humidity']; ?>%</p>
    <p>Era: <?php echo $weather['wind']['speed']; ?> m/s</p>
</div>
<?php else: ?>
<div class="weather-widget" style="background:#f0f4f8; padding:1rem; border-radius:10px; margin-bottom:1rem; text-align:center;">
    <p>Nuk mund të merren të dhënat e motit.</p>
</div>
<?php endif; ?>