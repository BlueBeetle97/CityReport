<?php
    if(isset($_GET['image_id'])) {
        try {
            $conn = new PDO('mysql:host=localhost;dbname=city_report', 'root');
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = 'SELECT imageData, imageType FROM reports WHERE report_id=?';
            $stmt = $conn->prepare($query);
            $stmt->execute([$_GET['image_id']]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            $conn = null;
            if ($image) {
                header('Content-type: ' . $image['imageType']);
                echo $image['imageData'];
            }
        } catch (PDOException $e) {
            $log = fopen("log.txt", "w") or die($empty_table);
            fwrite($log, $e);
            fclose($log);
        }
    }
?>