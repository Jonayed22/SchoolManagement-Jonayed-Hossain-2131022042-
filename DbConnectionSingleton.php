<?php
class DbConnectionSingleton {
    private static $instance = null;
    private $connection;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        $servername = "localhost";
        $username = "root"; 
        $password = ""; 
        $database = "school_management_system"; 

        $this->connection = new mysqli($servername, $username, $password, $database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    // Static method to return the single instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DbConnectionSingleton();
        }
        return self::$instance;
    }

    // Method to return the database connection
    public function getConnection() {
        return $this->connection;
    }
}
?>
