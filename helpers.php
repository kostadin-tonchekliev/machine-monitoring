<?php

    function ChangeMachineStatus($machineId) {
        global $mysqli;

        $currentStatusResult = $mysqli -> query("SELECT * FROM machines WHERE machineId = $machineId ;");
        while($currentRow = $currentStatusResult->fetch_assoc()){
            $currentStatus = $currentRow['machineStatus'];
        }

        if ($currentStatus != null) {
            if ($currentStatus == 'online'){
                $mysqli -> query("UPDATE machines SET machineStatus = 'offline' WHERE machineid = ".$machineId);
            } elseif ($currentStatus == 'offline'){
                $mysqli -> query("UPDATE machines SET machineStatus = 'online' WHERE machineid = ".$machineId);
            }
        } else{
            echo "[Err] Invalid machine ID: $machineId";
        }
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

        $gpioPinsResult = $mysqli -> query("SELECT machines.machineStatus, gpioPins.onPin, gpioPins.offPin FROM machines INNER JOIN gpioPins ON machines.machineId = gpioPins.id ;");

        while($pinRow = $gpioPinsResult->fetch_assoc()){
            exec("sudo raspi-gpio set ".$pinRow['onPin']." op");
            exec("sudo raspi-gpio set ".$pinRow['offPin']." op");
            if($pinRow['machineStatus'] == 'online'){
                exec("sudo raspi-gpio set ".$pinRow['offPin']." dl");
                exec("sudo raspi-gpio set ".$pinRow['onPin']." dh");
            } elseif ($pinRow['machineStatus'] == 'offline'){
                exec("sudo raspi-gpio set ".$pinRow['onPin']." dl");
                exec("sudo raspi-gpio set ".$pinRow['offPin']." dh");
            }
        }
    }


?>