<?php
    require __DIR__ . '/helpers.php';
    require __DIR__ . '/dbCredentials.php';

    $mysqli = new mysqli($servername, $username, $password, $db);

    $allMachines = returnMachineIds();

    foreach ($allMachines as $id){
        echo"----------------------------------<br>";
        echo "Checking machine id: $id <br/>";
        $finalData = processData($id);
        echo "Uptime Percentage: $finalData[0]%<br>";
        echo "Downtime Percentage: $finalData[1]%<br>";
    }
?>