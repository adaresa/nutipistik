<?php
session_start();
session_destroy();

if(isset($_COOKIE['remember'])) {
    unset($_COOKIE['remember']);
    setcookie('remember', null, -1, '/');
}

header('LOCATION:index.php');
exit;

?>