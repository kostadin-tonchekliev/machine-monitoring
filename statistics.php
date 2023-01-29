<?php
    require __DIR__ . '/helpers.php';
    require __DIR__ . '/dbCredentials.php';

    $mysqli = new mysqli($servername, $username, $password, $db);

    $allMachines = returnMachineIds();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Machine Statistics Page</title>
        <link rel="stylesheet" href="stylesheet.css">
    </head>
    <body>
        <div id="statistics">
            <?php
                foreach ($allMachines as $id){
                    $finalData = processData($id[0]);
                    echo "<div id=\"nameTag\">$id[1]";
                    echo "<div id=\"wrapper\">";
                    echo "<div id=\"uptime\" style=\"width:".$finalData[0]."%;\">&nbsp;</div>";
                    echo "<div id=\"downtime\" style=\"width:".$finalData[1]."%;\">&nbsp;</div>";
                    echo "</div>";
                    echo "</div>";
                    echo "<br>";
                }
            ?>
        </div>
        <br>
        <div id="statistics">
                <h3>Legend</h3>
                <div id="legend">
                    <div id="uptimeLegend">Uptime</div><div id="downtimeLegend">Downtime</div>
                </div>
        </div>
        <br>
        <div id="navigationMenu">
            <a href="/index.php"><button class="navButton">HomePage</button></a>
        </div>
    </body>
</html>