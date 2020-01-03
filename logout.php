<?php
session_start();

if ($_SESSION['login'] == true) {
    $_SESSION['login'] = false;
    $_SESSION['service'] = null;
    http_response_code(200);
} else {
    http_response_code(404);
}

?>