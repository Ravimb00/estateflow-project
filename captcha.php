<?php

session_start();

header('Content-type:image/png');

/* IMAGE */

$image = imagecreatetruecolor(
160,
50
);

/* COLORS */

$bg = imagecolorallocate(
$image,
15,
23,
42
);

$textColor = imagecolorallocate(
$image,
255,
255,
255
);

$lineColor = imagecolorallocate(
$image,
59,
130,
246
);

/* BACKGROUND */

imagefilledrectangle(
$image,
0,
0,
160,
50,
$bg
);

/* CAPTCHA */

$chars =
'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

$captcha = substr(
str_shuffle($chars),
0,
6
);

$_SESSION['captcha_text'] =
$captcha;

/* NOISE */

for($i=0;$i<6;$i++){

imageline(
$image,
rand(0,160),
rand(0,50),
rand(0,160),
rand(0,50),
$lineColor
);

}

/* TEXT */

imagestring(
$image,
5,
38,
18,
$captcha,
$textColor
);

/* OUTPUT */

imagepng($image);

imagedestroy($image);

?>