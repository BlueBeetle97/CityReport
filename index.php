<?php
session_start();

if (!isset($_SESSION['login'])) {
    $_SESSION['login'] = false;
    readfile('./html/login.html');
}

else if ($_SESSION['login'] == false) {
    if (isset($_POST['login'])) {

    } else {
        readfile('./html/login.html');
    }
}

else if ($_SESSION['login'] == true) {
    readfile('./html/list.html');
}

?>