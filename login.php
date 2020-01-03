<?php
session_start();

if ($_SESSION['login'] == false) {
    if(isset($_POST['username']) && 
    isset($_POST['password']) &&
    isset($_POST['service_id']) 
    ) {
        try {
            $conn = new PDO('mysql:host=localhost;dbname=city_report', 'root');
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = 'SELECT * FROM employees WHERE username=? AND password=PASSWORD(?) AND service_id=?';
            $stmt = $conn->prepare($query);
            $stmt->execute([$_POST['username'], $_POST['password'], $_POST['service_id']]);
            $userExists = $stmt->fetch(PDO::FETCH_ASSOC);
            $conn = null;
            if ($userExists) {
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $userExists['user_id'];
                http_response_code(200);
                return;
            } else {
                http_response_code(500);
            }
            
        } catch(PDOException $e) {
            $log = fopen("log.txt", "w") or die(http_response_code(401));
            fwrite($log, $e);
            fclose($log);
            http_response_code(400);
        }
    } else {
        http_response_code(500);
    }
} else {
    http_response_code(404);
}

?>