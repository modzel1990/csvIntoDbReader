<?php

class dbConn 
{
    private $server;
    private $username;
    private $password;
    private $dbname;
    private $conn;

    // This must be dynamic, provide credentials via command - prompt for it
    function __construct($servername, $username, $password, $dbname) 
    {
        $this->server = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
    }

    public function dbConnect() 
    {
        // Create connection
        $this->conn = new mysqli($this->server, $this->username, $this->password, $this->dbname);
        // Check connection
        if ($this->conn->connect_error) {
        die("Connection failed: " . $this->conn->connect_error);
        }

        return $this->conn;
    }

    public function dbPrepare($sql)
    {
        return $this->conn->prepare($sql);
    }

    public function dbInsert($sql)
    {
        if ($this->conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $this->conn->error;
        }
    }

    public function dbClose() 
    {
        $this->conn->close();
    }
}

?>