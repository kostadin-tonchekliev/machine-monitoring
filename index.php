<?php

    require __DIR__ . '/helpers.php';
    require __DIR__ . '/dbCredentials.php';

    $mysqli = new mysqli($servername, $username, $password, $db);

    $machineId = $_GET['machineid'];
    $action = $_GET['action'];

    if ($mysqli -> connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
        exit();
    }

    exec("sudo raspi-gpio set 21 op");
    exec("sudo raspi-gpio set 21 dh");

    if(array_key_exists('changeStatus', $_POST)) {
        changeStatus();
    }

    if ($machineId != null && $action != null ) {
        if ($machineId != null && $action != "ChangeStatus" ) {
            echo "[Err] Invalid action: $action";
        } else {
            ChangeMachineStatus($machineId);
        }
    }

    initializeLeds();
    storeStatuses();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Machine Status Page</title>
        <link rel="stylesheet" href="stylesheet.css">
    </head>
    <body >
      <div id="machineData">
        <form method="post">
            <table class="mainTable">
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
                        if ($row["machineStatus"] == 'online'){
                            echo "<th style=\"color:green;\">".$row["machineStatus"]."</th>" ;
                        } elseif ($row["machineStatus"] == 'offline'){
                            echo "<th style=\"color:red;\">".$row["machineStatus"]."</th>" ;
                        }
                        echo "<th><button type=\"submit\" value=\"$row[machineId]\" name=\"changeStatus\">Change Me</button></th>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </form>
      </div>
      <br/>
      <div id="offlineMachines">
        <?php
            $offMachines = getOfflineMachines();
            if (count($offMachines) != 0){
                foreach ($offMachines as $offId){
                    $result = getOfflineData($offId);
                    echo "<div id=\"offlineResult\">";
                    echo "<div id=\"offlineResultName\">".$result[0]."</div>";
                    echo "<div id=\"offlineResultCount\">".$result[1]."</div>";
                    echo "</div>";
                }
            }else {
                echo "<div id=\"offlineResult\"'>No offline machines</div>";
            }
        ?>
      </div>
      <div id="navigationMenu">
        <a href='/statistics.php'><button class="navButton">Statistics Page</button></a>
      </div>
    </body>
</html>