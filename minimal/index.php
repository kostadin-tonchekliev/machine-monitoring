<?php
    require __DIR__ . '/../helpers/functionHelpers.php';
    require __DIR__ . '/../helpers/dbCredentials.php';

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
    <body>
    <div id="machineData">
        <form method="post">
            <table class="mainTable", border=1>
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
      <div id="navigationMenu">
        <a href='/minimal/statistics.php'><button class="navButton">Statistics Page</button></a>
      </div>
    </body>
</html>