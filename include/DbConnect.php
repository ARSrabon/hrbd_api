<?php
/**
 * Created by PhpStorm.
 * User: msrabon
 * Date: 1/3/17
 * Time: 9:42 PM
 */

class DbConnect {

    private $conn;

    function __construct() {
    }

    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect() {
        include_once dirname(__FILE__) . '/Config.php';

        // Connecting to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        // Check for database connection error
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        // returing connection resource
        return $this->conn;
    }

}

?>