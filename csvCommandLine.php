<?php
require_once('csvReader.php');
require_once('dbConn.php');
// argv 1 = csv file name
// argv 2 = db host
// argv 3 = db user
// argv 4 = db pass
// argv 5 = db name
// argv 6 = (optional) test
if(isset($argv[1]) && isset($argv[2]) && isset($argv[3]) && isset($argv[4]) && isset($argv[5])) {
    $db = new dbConn($argv[2], $argv[3], $argv[4], $argv[5]);
    if(!$db->dbConnect()) {
        echo 'Failed to connect to DB';
        exit();
    }
}

// Check if code should be run in 'test' mode
if(isset($argv[6]) && $argv[6] == 'test') {
    $test = $argv[6];
} else {
    $test = '';
}

// Get file name into variable
if(isset($argv[1])) {
    $filename = $argv[1];
}
// $filename = $_FILES['csvFile']['name'];
$fileType = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
if($fileType != "csv") {
    echo "Sorry, only CSV files are allowed. Your file extension is: " . $fileType;
    exit();
}

$csvReader = new csvReader($filename);
$data = $csvReader->readCsv();
$csvReader->insertCsvDataToDb($data, $db, $test);
?>
