<?php
    try {
        $conn = new PDO('mysql:host=localhost;dbname=city_report', 'root');
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = 'SELECT service_id,title FROM services';
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $table = $stmt->fetchAll();
        $conn = null;
        if ($table) {
            foreach ($table as $row) {
                echo '<option value="'.$row['service_id'].'">'.$row['title'].'</option>';
            }
        }
    } catch (PDOException $e) {
        $log = fopen("log.txt", "w") or die($empty_table);
        fwrite($log, $e);
        fclose($log);
    }
?>