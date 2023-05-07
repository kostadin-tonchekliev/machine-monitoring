<?php
require __DIR__ . '/helpers.php';
require __DIR__ . '/dbCredentials.php';

$mysqli = new mysqli($servername, $username, $password, $db);

if ($mysqli -> connect_errno) {
    print("Failed to connect to MySQL: " . $mysqli -> connect_error);
    exit();
}

$machineId = $_GET['machineid'];
$action = $_GET['action'];

if ($machineId != null && $action != null ) {
    if ($action == "ChangeStatus" ) {
        changeMachineStatus($machineId);
        printCurrentStatus($machineId);
        exec('php index.php');
    } elseif ($action == "GetStatus") {
        printCurrentStatus($machineId);
    } else {
        print("[Err] Invalid action: $action");
    }
} else {
    print("You need to provide all required parameters in order to run");
}
