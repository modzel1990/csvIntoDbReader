<?php

class csvReader 
{

    public $csvData;
    public $skipped;
    public $processed;
    public $successful;

    function __construct($filepath) 
    {
        $this->csvData = array_map('str_getcsv', file($filepath));
    }

    public function validateCsvData($val, $key) 
    {
        // Stock and cost_in_gb not set
        if(!isset($val[4]) || !isset($val[3])) {
            $this->skipped[$key] = $val;
            return false;
        }

        // Stock and cost_in_gb rules
        if(($val[4] < 5 && $val[3] < 10) || $val[4] > 1000) {
            $this->skipped[$key] = $val;
            return false;
        }

        return true;
    }

    public function testInput($data) 
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public function readCsv() {
        return $this->csvData;
    }

    public function insertCsvDataToDb($data, $db, $test) {
        $headers = [];
        $row = 0;

        foreach($data as $key => $val) {
            if($key == 0) {
                // Headers
                // I am taking my headers separately as based on them one could do all the validation, checks and inserts
                // This could potentially help when someone provides same excel file but with different column order
                // Code could be smart and handle it by providing correct values to correct columns based on column name
                // At same place would be beneficial to add check if headers are set, or if we are accessing values
                // As if headers are not set and excel file consist just of values, we could throw an error or skip this step
                foreach($val as $subKey => $subVal) {
                    $headers[$subKey] = $subVal;
                }
                continue;
            }
            
            if($this->validateCsvData($val, $key)) {               
                if(isset($test) && $test != 'test') {
                    $date = date("Y-m-d H:i:s");
                    
                    // Sanitization
                    if(isset($val[0])) { 
                        $val[0] = $this->testInput($val[0]);
                    }
                    if(isset($val[1])) { 
                        $val[1] = $this->testInput($val[1]);
                    }
                    if(isset($val[2])) { 
                        $val[2] = $this->testInput($val[2]);
                    }
                    if(isset($val[3])) { 
                        $val[3] = $this->testInput($val[3]);
                    }
                    if(isset($val[4])) { 
                        $val[4] = $this->testInput($val[4]);
                        $val[4] = is_numeric($val[4]) ? $val[4] : 0;
                    }
                    if(isset($val[5])) { 
                        $val[5] = $this->testInput($val[5]);
                    }
            
                    // Check if discontinued (this check ideally would be inside validator)
                    $val[5] = (isset($val[5]) && $val[5] == 'yes') ? $date : null;
                    
                    $stmt = $db->dbPrepare("INSERT INTO tblproductdata (product_name, product_desc, product_code, created_at, discontinued, stock, cost_in_gb) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssid", $val[1], $val[2], $val[0], $date, $val[5], $val[3], $val[4]);
                    $stmt->execute();
                }

                $this->successful[] = $val;
            }
            $this->processed[] = $val;
            $row++;
        }
        // print_r("\n\nProcessed: ");
        // print_r($this->processed);
        // print_r("\n\nSuccessful: ");
        // print_r($this->successful);
        print_r("\n\nSkipped: \n\n");
        print_r($this->skipped);
        print_r("\n\nProcessed Count: ");
        print_r(count($this->processed));
        print_r("\n\nSuccessful Count: ");
        print_r(count($this->successful));
        print_r("\n\nSkipped Count: ");
        print_r(count($this->skipped));
        print_r("\n\n");
    }
}

?>