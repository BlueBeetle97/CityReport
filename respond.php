<?php
session_start();
if (
    $_SESSION['login'] != true ||
    !isset($_SESSION['user_id']) ||
    !isset($_POST['report_id'])
) {
    http_response_code(404);
    return;
}

try {
    $conn = new PDO('mysql:host=localhost;dbname=city_report', 'root');
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['responce'])) {
        $query = 'UPDATE reports SET status=? WHERE report_id=? AND service_id=(SELECT service_id FROM employees WHERE user_id=?)';
        $stmt = $conn->prepare($query);
        if ($stmt->execute([$_POST['responce'], $_POST['report_id'], $_SESSION['user_id']])) {
            http_response_code(200);
        } else {
            http_response_code(403);
        }
    } elseif (isset($_POST['forward'])) {
        $query = 'INSERT INTO pushed_reports (report_id, from_service_id, to_service_id, user_id, og_timestamp) VALUES (?, (SELECT service_id FROM employees WHERE user_id=?), ?, ?, (SELECT timestamp FROM reports WHERE report_id=?))';
        $stmt = $conn->prepare($query);
        if ($stmt->execute([$_POST['report_id'], $_SESSION['user_id'], $_POST['forward'], $_SESSION['user_id'], $_POST['report_id']])) {
            $query = 'UPDATE reports SET status="0", service_id=? WHERE report_id=? AND service_id=(SELECT service_id FROM employees WHERE user_id=?)';
            $stmt = $conn->prepare($query);
            if ($stmt->execute([$_POST['forward'], $_POST['report_id'], $_SESSION['user_id']])) {
                http_response_code(200);
            } else {
                http_response_code(403);
            }
        } else {
            http_response_code(403);
        }
    } elseif (isset($_POST['delete']) && $_POST['delete'] == true) {
        $query = 'DELETE FROM reports WHERE service_id=(SELECT service_id FROM employees WHERE user_id=?) AND report_id=?';
        $stmt = $conn->prepare($query);
        if ($stmt->execute([$_SESSION['user_id'], $_POST['report_id']])) {
            http_response_code(200);
        } else {
            http_response_code(403);
        }
    } else {
        http_response_code(404);
        return;
    }
} catch (PDOException $e) {
    $log = fopen("log.txt", "w") or die();
    fwrite($log, $e);
    fclose($log);
    http_response_code(404);
    return;
}
