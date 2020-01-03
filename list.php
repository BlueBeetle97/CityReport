<?php
session_start();

if ($_SESSION['login'] == true && 
    isset($_SESSION['user_id'])
    ){
    
    try {
        $conn = new PDO('mysql:host=localhost;dbname=city_report', 'root');
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = 'SELECT * FROM reports WHERE service_id=(SELECT service_id FROM employees WHERE user_id=?) ORDER BY status ASC';
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $reports = $stmt->fetchAll();

        $query = 'SELECT * FROM reports WHERE report_id=(SELECT report_id FROM pushed_reports WHERE from_service_id=(SELECT service_id FROM employees WHERE user_id=?))';
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $pushed = $stmt->fetchAll();
        $pushed = array_reverse($pushed);

        $conn = null;

        if ($pushed) {
            foreach ($pushed as $row) {
                echo '<tr style="cursor: pointer;">';
			    echo '<th scope="row">' . $row['report_id'] . '</th>';
                echo '<td>' . $row['title'] . '</td>';
                $options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0'));
                $context  = stream_context_create($options);
                $str = file_get_contents('http://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=el&lat=' . $row['lat'] . '&lon=' . $row['lon'] . '&limit=1', false, $context);
                $json = json_decode($str, true);
                $lo_name = $json['display_name'];
                if ($json['name']) {
                    $lo_name = $json['name'];
                } elseif ($json['village']) {
                    $lo_name = $json['village'];
                } elseif ($json['town']) {
                    $lo_name = $json['town'];
                } elseif ($json['address']['hamlet']) {
                    $lo_name = $json['address']['hamlet'];
                } elseif ($json['address']['county']) {
                    $lo_name = $json['address']['county'];
                } elseif ($json['address']['state_district']) {
                    $lo_name = $json['address']['state_district'];
                } elseif ($json['address']['state']) {
                    $lo_name = $json['address']['state'];
                } else {
                    $lo_name = $row['lat'] . ', ' . $row['lon'];
                }
                echo '<td>' . $lo_name . '</td>';
                
                echo '<td>'. $row['timestamp'] . '</td>';
                
                echo '<td><i class="fas fa-share"></i> Προωθήθηκε στην αρμόδια υπηρεσία</i></td>';
            }
        }

        if ($reports) {
            foreach ($reports as $row) {
                echo '<tr style="cursor: pointer;" onclick="check('. $row['report_id'] .')">';
			    echo '<th scope="row">' . $row['report_id'] . '</th>';
                echo '<td>' . $row['title'] . '</td>';
                $options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0'));
                $context  = stream_context_create($options);
                $str = file_get_contents('http://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=el&lat=' . $row['lat'] . '&lon=' . $row['lon'] . '&limit=1', false, $context);
                $json = json_decode($str, true);
                $lo_name = $json['display_name'];
                if ($json['name']) {
                    $lo_name = $json['name'];
                } elseif ($json['village']) {
                    $lo_name = $json['village'];
                } elseif ($json['town']) {
                    $lo_name = $json['town'];
                } elseif ($json['address']['hamlet']) {
                    $lo_name = $json['address']['hamlet'];
                } elseif ($json['address']['county']) {
                    $lo_name = $json['address']['county'];
                } elseif ($json['address']['state_district']) {
                    $lo_name = $json['address']['state_district'];
                } elseif ($json['address']['state']) {
                    $lo_name = $json['address']['state'];
                } else {
                    $lo_name = $row['lat'] . ', ' . $row['lon'];
                }
                echo '<td>' . $lo_name . '</td>';
                
                echo '<td>'. $row['timestamp'] . '</td>';
                
                switch ($row['status']) {
                    case '0':
                        echo '<td><i class="fas fa-exclamation"></i> Άλυτο</td>';
                        break;
                    case '2':
                        echo '<td><i class="fas fa-check-circle"></i> Επιδιορθώθηκε</i></td>';
                        break;
                    case '3':
                        echo '<td><i class="fas fa-ban"></i> Απορρίφθηκε</i></td>';
                        break;
                    case '1':
                        echo '<td><i class="fas fa-wrench"></i> Υπό επιδιόρθωση</td>';
                        break;
                    default:
                        echo '<td>N/A</td>';
                        break;
                }
            }
        }
        
    } catch (PDOException $e) {
        $log = fopen("log.txt", "w") or die($empty_table);
        fwrite($log, $e);
        fclose($log);
    }

}
?>