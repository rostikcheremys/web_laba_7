<?php
$fontFile = 'fonts/Roboto/Roboto-Bold.ttf';
$cityCode = $_GET["gorod"];

$curl = curl_init();
$url = "http://www.gismeteo.ua/city/hourly/" . $cityCode . "/";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
$out = curl_exec($curl);
curl_close($curl);

preg_match('/<meta name="keywords" content=".*?погода в ([^,]+)/', $out, $cityMatch);
$city = $cityMatch[1] ?? 'Місто не знайдено';

preg_match('/"date":"(\d{2}\.\d{2}\.\d{4})/', $out, $dateMatch);
$date = $dateMatch[1] ?? 'Дата не знайдена';

preg_match_all('/<temperature-value value="([-]?\d+)"/', $out, $temperatureMatches);
$temperatureValues = [];
for ($i = 7; $i < 15; $i++) {
    $temperatureValues[] = (int) ($temperatureMatches[1][$i] ?? 0);
}

$im = imagecreatetruecolor(500, 200);
$black = imagecolorallocate($im, 0, 0, 0);
$red = imagecolorallocate($im, 255, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
$yellow = imagecolorallocate($im, 255, 255, 0);
$blue = imagecolorallocate($im, 0, 0, 255);

imagefilledrectangle($im, 0, 0, 499, 199, $black);

imageantialias($im, true);

$moon = @imagecreatefrompng('img/moon_sm.png') ?: die("Помилка: Зображення moon_sm.png не знайдено.");
$sun = @imagecreatefrompng('img/sun_sm.png') ?: die("Помилка: Зображення sun_sm.png не знайдено.");
$left = @imagecreatefrompng('img/left.png') ?: die("Помилка: Зображення left.png не знайдено.");
$center = @imagecreatefrompng('img/center.png') ?: die("Помилка: Зображення center.png не знайдено.");
$right = @imagecreatefrompng('img/right.png') ?: die("Помилка: Зображення right.png не знайдено.");

imagecopy($im, $left, 105, 0, 0, 0, imagesx($left), imagesy($left));
imagecopy($im, $center, 205, 0, 0, 0, imagesx($center), imagesy($center));
imagecopy($im, $right, 305, 0, 0, 0, imagesx($right), imagesy($right));

imagecopy($im, $moon, 25, 10, 0, 0, imagesx($moon), imagesy($moon));
imagecopy($im, $moon, 415, 10, 0, 0, imagesx($moon), imagesy($moon));
imagecopy($im, $sun, 225, 5, 0, 0, imagesx($sun), imagesy($sun));

$startX = 50;
$startY = 150;
$stepX = 50;
$maxTemp = max($temperatureValues);
$minTemp = min($temperatureValues);

imageline($im, $startX, $startY, $startX + $stepX * 7, $startY, $blue);

for ($i = 0; $i < 8; $i++) {
    $hour = $i * 3;
    $x = $startX + $i * $stepX;
    imagettftext($im, 10, 0, $x - 5, $startY + 15, $blue, $fontFile, $hour);
}

for ($i = 0; $i < 7; $i++) {
    $x1 = $startX + $i * $stepX;
    $y1 = $startY - (($temperatureValues[$i] - $minTemp) / ($maxTemp - $minTemp)) * 100;
    $x2 = $startX + ($i + 1) * $stepX;
    $y2 = $startY - (($temperatureValues[$i + 1] - $minTemp) / ($maxTemp - $minTemp)) * 100;
    
    imageline($im, $x1, $y1, $x2, $y2, $red);
    
    imagettftext($im, 10, 0, $x1 - 5, $y1 - 5, $red, $fontFile, ($temperatureValues[$i] > 0 ? '+' : '') . $temperatureValues[$i]);
}

imagettftext($im, 12, 0, 120, 185, $white, $fontFile, "Погода в: $city");
imagettftext($im, 12, 0, 280, 185, $white, $fontFile, "$date");

header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
?>