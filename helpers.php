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

    function storeStatuses(){
        global $mysqli;

        $allMachines = $mysqli -> query("SELECT * FROM machines ;");
        while($machineRow = $allMachines -> fetch_assoc()){
            $mysqli -> query("INSERT INTO statusLog(machineId, newStatus, dateTime) VALUES (".$machineRow['machineId'].", '".$machineRow['machineStatus']."', CURRENT_TIMESTAMP);");
        }
    }

    function returnMachineIds(){
        global $mysqli;
        $tmpArray = array();

        $machineData = $mysqli -> query("SELECT * FROM machines ;");

        while($machineRow = $machineData -> fetch_assoc()){
            array_push($tmpArray, $machineRow['machineId']);
        }

        return $tmpArray;
    }

    function getTimeDiff($start, $end){
        $diff =  $start -> diff($end);
        $total_seconds = ($diff->days * 24 * 60); 
        $total_seconds += ($diff->h * 60); 
        $total_seconds += ($diff->i * 60);
        $total_seconds += $diff->s;

        return $total_seconds;
    }

    function processData($machineId){
        global $mysqli;
        $downtime = 0;
        $uptime = 0;
        $index = 0;
        $statusData = array();

        $statusLogs = $mysqli -> query("SELECT * FROM statusLog WHERE machineId = ".$machineId);

        while($statusRow = $statusLogs->fetch_assoc()){
            array_push($statusData, array($statusRow['newStatus'], $statusRow['dateTime']));
        }

        $now_time = new Datetime(date('Y-m-d H:i:s', time()));
        
        while($index != count($statusData)){
            $start_time = new DateTime($statusData[$index][1]);
            $tmpIndex = $index;
            $checkForEnd = False;

            if ($statusData[$index][0] == 'offline'){
                while($checkForEnd == False){
                    $tmpIndex++;
                    if ($statusData[$tmpIndex][0] == 'online' || $tmpIndex == count($statusData)-1){
                        $checkForEnd = True;
                    }
                }
                $end_time = new DateTime($statusData[$tmpIndex][1]);
                $tmp = getTimeDiff($start_time, $end_time);
                $downtime += $tmp;
                $index = $tmpIndex;
            }elseif ($statusData[$index][0] == 'online'){
                while($checkForEnd == False){
                    $tmpIndex++;
                    if ($statusData[$tmpIndex][0] == 'offline' || $tmpIndex == count($statusData)-1){
                        $checkForEnd = True;
                    }
                }
                $end_time = new DateTime($statusData[$tmpIndex][1]);
                $tmp = getTimeDiff($start_time, $end_time);
                $uptime += $tmp;
                $index = $tmpIndex;
            }

            if($index == count($statusData)-1){
                if($statusData[$index][0] == 'offline'){
                    $downtime += getTimeDiff($start_time, $now_time);
                }elseif($statusData[$index][0] == 'online'){
                    $uptime += getTimeDiff($start_time, $now_time);
                }
                $index++;
            }
        }
        
        $totalTime = $uptime + $downtime;
        $uptimePercent = ($uptime / $totalTime) * 100;
        $downtimePercent = ($downtime / $totalTime) * 100;
        return [round($uptimePercent), round($downtimePercent)];
    }