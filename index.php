<?php
    require __DIR__ . '/helpers/functionHelpers.php';
    require __DIR__ . '/helpers/dbCredentials.php';

    $mysqli = new mysqli($servername, $username, $password, $db);

    if ($mysqli -> connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
        exit();
    }

    if(array_key_exists('changeStatus', $_POST)) {
        changeStatus();
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
                    <th>Номер</th>
                    <th>Име</th>
                    <th>Статус</th>
                    <th>Промени статус</th>
                </tr>
                <?php
                    $result = $mysqli -> query("SELECT * FROM machines");
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>" ;
                        echo "<th>".$row["machineId"]."</th>" ;
                        echo "<th>".$row["machineName"]."</th>" ;
                        if ($row["machineStatus"] == 'online'){
                            echo "<th style=\"color:green;\">".онлайн."</th>" ;
                        } elseif ($row["machineStatus"] == 'offline'){
                            echo "<th style=\"color:red;\">".офлайн."</th>" ;
                        }
                        echo "<th><button type=\"submit\" value=\"$row[machineId]\" name=\"changeStatus\">Промени ме</button></th>";
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
        <a href='/statistics.php'><button class="navButton">Статистики</button></a>
      </div>
    </body>
</html>
