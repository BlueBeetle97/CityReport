
<?php

if (is_uploaded_file($_FILES['image']['tmp_name']) &&
    isset($_POST['title']) &&
    isset($_POST['desc']) &&
    isset($_POST['service_id']) &&
    isset($_POST['lat']) &&
    isset($_POST['lon'])
    ) {

    $options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0'));
    $context  = stream_context_create($options);
    $str = file_get_contents('http://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=el&lat=' . $_POST['lat'] . '&lon=' . $_POST['lon'] . '&limit=1', false, $context);
    $json = json_decode($str, true);

    if ($json['address']['county'] != 'Δήμος Σάμου') {
        http_response_code(401);
        echo 'upload unsuccessful, unsupported location';
        return;
    }

    $imgData = file_get_contents($_FILES['image']['tmp_name']);
    $imageProperties = getimageSize($_FILES['image']['tmp_name']);

    try {
        $conn = new PDO('mysql:host=localhost;dbname=city_report', 'root');
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = 'INSERT INTO reports (service_id, title, description, imageData, imageType, lat, lon) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($query);
        $date = date_create();
        $t_stamp = date_timestamp_get($date);
        if (!$stmt->execute([$_POST['service_id'], $_POST['title'], $_POST['desc'], $imgData, $imageProperties['mime'], $_POST['lat'], $_POST['lon']])) {
            http_response_code(402);
            echo 'upload unsuccessful, db error';
        } else {
            http_response_code(200);
            echo 'upload successful';
        }
        
    } catch(PDOException $e) {
        $log = fopen("log.txt", "w") or die('upload unsuccessful, no log :(');
        fwrite($log, $e);
        fclose($log);
        http_response_code(403);
        echo 'upload unsuccessful, check log';
        return;;
    }

} else {
    http_response_code(404);
    echo 'upload unsuccessful, wrong parameters';
}
?>
