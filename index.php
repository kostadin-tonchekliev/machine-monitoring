<?php
    $servername = "localhost";
    $username = "admin";
    $password = "adminpassword123";
    $db = 'monitoring_app';

    $mysqli = new mysqli($servername, $username, $password, $db);

    if ($mysqli -> connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
        exit();
    }

    if(array_key_exists('changeStatus', $_POST)) {
        changeStatus();
    }

    function changeStatus(){
        global $mysqli;
        $machineId = $_POST['changeStatus'];

        $statusResult = $mysqli -> query("SELECT machineStatus FROM machines WHERE machineId = ".$machineId);

        while($currentRow = $statusResult->fetch_assoc()){
            $currentStatus = $currentRow['machineStatus'];
        }
        
        if($currentStatus == 'online') {
            $mysqli -> query("UPDATE machines SET machineStatus = 'offline' WHERE machineid = ".$machineId);
        } elseif ($currentStatus == 'offline') {
            $mysqli -> query("UPDATE machines SET machineStatus = 'online' WHERE machineid = ".$machineId);
        }
    }

    function initializeLeds(){
        global $mysqli;

        $gpioPinsResult = $mysqli -> query("SELECT machineStatus, gpioPin FROM machines");

        while($pinRow = $gpioPinsResult->fetch_assoc()){
            exec("sudo raspi-gpio set ".$pinRow['gpioPin']." op");
            if($pinRow['machineStatus'] == 'online'){
                exec("sudo raspi-gpio set ".$pinRow['gpioPin']." dh");
            } elseif ($pinRow['machineStatus'] == 'offline'){
                exec("sudo raspi-gpio set ".$pinRow['gpioPin']." dl");
            }
        }
    }

    initializeLeds();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Machine Status Page</title>
    </head>
    <body>
      <div>
        <form method="post">
            <table border=1>
                <tr>
                    <th>Machine ID</th>
                    <th>Machine Name</th>
                    <th>Machine Status</th>
                    <th>Change Status</th>
                </tr>
                <?php
                    $result = $mysqli -> query("SELECT * FROM machines");
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>" ;
                        echo "<th>".$row["machineId"]."</th>" ;
                        echo "<th>".$row["machineName"]."</th>" ;
                        echo "<th>".$row["machineStatus"]."</th>" ;
                        echo "<th><button type=\"submit\" value=\"$row[machineId]\" name=\"changeStatus\">Change Me</button></th>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </form>
      </div>
    </body>
</html>