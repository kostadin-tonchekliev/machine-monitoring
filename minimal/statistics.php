<?php
    require __DIR__ . '/../helpers/functionHelpers.php';
    require __DIR__ . '/../helpers/dbCredentials.php';

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
    <div id="navigationMenu">
        <a href="/minimal/index.php"><button class="navButton">HomePage</button></a>
    </div>
    </body>
</html>