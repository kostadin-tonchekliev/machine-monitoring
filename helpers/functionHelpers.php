<?php

    function changeMachineStatus($machineId) {
        global $mysqli;

        $currentStatusResult = $mysqli -> query("SELECT * FROM machines WHERE machineId = $machineId ;");
        while($currentRow = $currentStatusResult->fetch_assoc()){
            $currentStatus = $currentRow['machineStatus'];
        }

        if ($currentStatus != null) {
            if ($currentStatus == 'online'){
                $mysqli -> query("UPDATE machines SET machineStatus = 'offline' WHERE machineid = ".$machineId);
                writeToFile("Machine ID $machineId went offline");
            } elseif ($currentStatus == 'offline'){
                $mysqli -> query("UPDATE machines SET machineStatus = 'online' WHERE machineid = ".$machineId);
                writeToFile("Machine ID $machineId went online");
            }
        } else{
            print("[Err] Invalid machine ID: $machineId");
        }
    }

    function printCurrentStatus($machineId) {
        global $mysqli;

        $currentStatusResult = $mysqli -> query("SELECT machineStatus FROM machines WHERE machineId = $machineId ;");
        while($currentRow = $currentStatusResult->fetch_assoc()){
            $currentStatus = $currentRow['machineStatus'];
        }

        if ($currentStatus != null) {
            print($currentStatus);
        } else{
            print("[Err] Invalid machine ID: $machineId");
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
            writeToFile("Machine ID $machineId went offline");
        } elseif ($currentStatus == 'offline') {
            $mysqli -> query("UPDATE machines SET machineStatus = 'online' WHERE machineid = ".$machineId);
            writeToFile("Machine ID $machineId went online");
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
            array_push($tmpArray, array($machineRow['machineId'], $machineRow['machineName']));
        }

        return $tmpArray;
    }

    function getOfflineMachines(){
        global $mysqli;
        $tmpArray = array();

        $offlineData = $mysqli -> query("SELECT * FROM machines WHERE machineStatus = \"offline\";");

        while($machineRow = $offlineData -> fetch_assoc()){
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
        $now_time = new Datetime(date('Y-m-d H:i:s', time()));

        $statusLogs = $mysqli -> query("SELECT * FROM statusLog WHERE machineId = ".$machineId);

        while($statusRow = $statusLogs->fetch_assoc()){
            array_push($statusData, array($statusRow['newStatus'], $statusRow['dateTime']));
        }

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
                $downtime += getTimeDiff($start_time, $end_time);
                $index = $tmpIndex;
            }elseif ($statusData[$index][0] == 'online'){
                while($checkForEnd == False){
                    $tmpIndex++;
                    if ($statusData[$tmpIndex][0] == 'offline' || $tmpIndex == count($statusData)-1){
                        $checkForEnd = True;
                    }
                }
                $end_time = new DateTime($statusData[$tmpIndex][1]);
                $uptime += getTimeDiff($start_time, $end_time);
                $index = $tmpIndex;
            }
            
            if($index == count($statusData)-1){
                $start_time = new DateTime($statusData[$index][1]);
                if($statusData[$index][0] == 'offline'){
                    $downtime += getTimeDiff($start_time, $now_time);
                }elseif($statusData[$index][0] == 'online'){
                    $uptime += getTimeDiff($start_time, $now_time);;
                }
                $index++;
            }
        }

        $totalTime = $uptime + $downtime;
        $hourUptime = round($uptime/60/60, 2);
        $hourDowntime = round($downtime/60/60, 2);
        $hourTotal = round($totalTime/60/60, 2);
        error_log("Uptime: $hourUptime Downtime: $hourDowntime");
        error_log("Total Uptime: $hourTotal");
        $uptimePercent = ($uptime / $totalTime) * 100;
        $downtimePercent = ($downtime / $totalTime) * 100;

        return [round($uptimePercent), round($downtimePercent), $hourTotal, $hourUptime, $hourDowntime];
    }

    function getOfflineData($machineId){
        global $mysqli;
        $offlineDataArray = array();
        $nowTime = new Datetime(date('Y-m-d H:i:s', time()));
        
        $offlineData = $mysqli -> query("SELECT machines.machineName, statusLog.newStatus, statusLog.dateTime FROM machines INNER JOIN statusLog ON machines.machineId = statusLog.machineId WHERE machines.machineId = ".$machineId." ORDER BY statusLog.id DESC;");

        while($offlineRow = $offlineData->fetch_assoc()){
            array_push($offlineDataArray, array($offlineRow['machineName'] ,$offlineRow['newStatus'], $offlineRow['dateTime']));
        }

        for($i=0; $i<count($offlineDataArray); $i++){
            $machineName = $offlineDataArray[$i][0];
            $indexStatus = $offlineDataArray[$i][1];
            $indexTime = $offlineDataArray[$i][2];
            
            if($offlineDataArray[$i+1][1] == 'online'){
                $offTime = new Datetime($indexTime);
                break;
            }
        }

        $timeDifference = round(getTimeDiff($offTime, $nowTime)/60, 2);

        return [$machineName, $timeDifference];
    }

    function writeToFile($text){
        if(!empty($_SERVER['REMOTE_ADDR'])){
            $outputText = "[".date("Y-m-d H:i:s")."][".$_SERVER['REMOTE_ADDR']."] ".$text;
            file_put_contents('/var/log/monitor.log', $outputText.PHP_EOL, FILE_APPEND);
        }
    }