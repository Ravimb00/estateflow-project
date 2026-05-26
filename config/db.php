<?php

$conn = mysqli_connect(
    "sql7.freesqldatabase.com",
    "sql7828340",
    "Uy7Jkg5Qsi",
    "sql7828340",
    3306
);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

?>