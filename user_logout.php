<?php

session_start();

/* CLEAR SESSION */

$_SESSION = array();

/* DESTROY */

session_destroy();

/* REDIRECT TO INDEX */

header("Location: index.php");

exit();

?>