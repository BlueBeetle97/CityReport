<?php
session_start();
error_reporting(0);
if (
    $_SESSION['login'] != true ||
    !isset($_SESSION['user_id']) ||
    !isset($_POST['report_id'])
) {
    http_response_code(404);
    return;
}


$empty_table =
    '
<tr>
    <td>N/A</td>
    <td>N/A</td>
    <td>N/A</td>        
</tr>
';
$image_id = null;
$map_link = null;

?>

<p>
    <div class="table">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Περιγραφή</th>
                    <th scope="col">Τοποθεσία</th>
                    <th scope="col">Ημερομηνία Υποβολής</th>
                    <th scope="col">Κατάσταση Προβλήματος Αίτησης</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    try {
                        $conn = new PDO('mysql:host=localhost;dbname=city_report', 'root');
                        // set the PDO error mode to exception
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $query = 'SELECT * FROM reports WHERE report_id=? AND service_id=(SELECT service_id FROM employees WHERE user_id=?)';
                        $stmt = $conn->prepare($query);
                        $stmt->execute([$_POST['report_id'], $_SESSION['user_id']]);
                        $table = $stmt->fetch();
                        $conn = null;
                        if ($table) {
                            $image_id = $table['report_id'];
                            echo '<td>' . $table['description'] . '</td>';
                            $options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0'));
                            $context  = stream_context_create($options);
                            $str = file_get_contents('http://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=el&lat=' . $table['lat'] . '&lon=' . $table['lon'] . '&limit=1', false, $context);
                            $json = json_decode($str, true);
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
                                $lo_name = $table['lat'] . ', ' . $table['lon'];
                            }
                            $map_link = 'https://maps.google.com/maps?q=' . $table['lat'] . ',' . $table['lon'];
                            echo '<td><a href="' . $map_link . '">' . $lo_name . '</a></td>';

                            echo '<td>' . $table['timestamp'] . '</td>';

                            switch ($table['status']) {
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
                        } else {
                            echo $empty_table;
                        }
                    } catch (PDOException $e) {
                        $log = fopen("log.txt", "w") or die($empty_table);
                        fwrite($log, $e);
                        fclose($log);
                        http_response_code(404);
                        return;
                    }
                    ?>
                </tr>

            </tbody>
        </table>
    </div>
</p>
<p>
    <div class="col-container">
        <div class="col">
            <img src="view_image.php?image_id=<?php echo $image_id; ?>" class="img-fluid" alt="Responsive image" style="height: 25rem">
        </div>
        <div class="col">
            <!--Google map-->
            <div id="map-container-google-1" class="z-depth-1-half map-container" style="height: 25rem;">
                <iframe src="<?php echo $map_link; ?>&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" style="border:0" allowfullscreen></iframe>
            </div>
            <!--Google Maps-->
        </div>
    </div>
</p>
<p>
    <div>

    </div>
</p>
<p>
    <div class="pagination justify-content-center">
        <div>
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Επίλυση Προβλήματος </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" style="cursor: pointer;" onclick="respond(1, <?php echo $_POST['report_id']; ?>)">
                        <font color="BLUE">Υπό επίλυση</font>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" style="cursor: pointer;" onclick="respond(2, <?php echo $_POST['report_id']; ?>)">
                        <font color="GREEN">Επιλύθηκε</font>
                    </a>
                </div>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-danger" onclick="respond(3, <?php echo $_POST['report_id']; ?>)">Απόρριψη Αίτησης</button>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-primary" onclick="modal_off()">Πίσω στην Λίστα Αναφορών</button>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Λάθος Υπηρεσία; </button>
                <div class="dropdown-menu">
                    <?php

                    try {
                        $conn = new PDO('mysql:host=localhost;dbname=city_report', 'root');
                        // set the PDO error mode to exception
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $query = 'SELECT service_id,title FROM services WHERE service_id!=(SELECT service_id FROM employees WHERE user_id=?)';
                        $stmt = $conn->prepare($query);
                        $stmt->execute([$_SESSION['user_id']]);
                        $table = $stmt->fetchAll();
                        $conn = null;
                        if ($table) {
                            foreach ($table as $service) {
                                echo '<a class="dropdown-item" style="cursor: pointer;" onclick="forward(' . $service['service_id'] . ', ' . $_POST['report_id'] . ')">' . $service['title'] . '</a>';
                            }
                        }
                    } catch (PDOException $e) {
                        $log = fopen("log.txt", "w") or die($empty_table);
                        fwrite($log, $e);
                        fclose($log);
                    }
                    ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" style="cursor: pointer;" onclick="remove(<?php echo $_POST['report_id'];?>)">
                        <font color="RED">Διαγραφή Αίτησης</font>
                    </a>
                </div>
            </div>
        </div>
    </div>
</p>