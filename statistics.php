<?php
    require __DIR__ . '/helpers/functionHelpers.php';
    require __DIR__ . '/helpers/dbCredentials.php';

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
                    echo "<div id=\"totalCounter\">Общо работно време: $finalData[2]</div>";
                    echo "<div id=\"wrapper\">";
                    echo "<div id=\"uptime\" style=\"width:".$finalData[0]."%;\">$finalData[3]</div>";
                    echo "<div id=\"downtime\" style=\"width:".$finalData[1]."%;\">$finalData[4]</div>";
                    echo "</div>";
                    echo "</div>";
                    echo "<br>";
                }
            ?>
        </div>
        <br>
        <div id="statistics">
                <h3>Легенда</h3>
                <div id="legend">
                    <div id="uptimeLegend">Време в което машината е работила</div><div id="downtimeLegend">Време в което машината не е работила</div>
                </div>
        </div>
        <br>
        <div id="navigationMenu">
            <a href="/index.php"><button class="navButton">Начална Старница</button></a>
        </div>
    </body>
</html>