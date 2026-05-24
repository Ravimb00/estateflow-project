<?php

session_start();

include 'config/db.php';

/* DESTROY USER SESSIONS */

mysqli_query(
$conn,
"UPDATE users
SET session_token=''"
);

header("Location:settings.php");
exit();

?>