<?php

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Load credentials from parent directory
        include dirname(__DIR__) . "/datos_conexion.php";
        
        // $host, $user, $pass, $database are expected from datos_conexion.php
        $this->connection = new mysqli($host, $user, $pass, $database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        $this->connection->query("SET NAMES utf8");
        $this->connection->set_charset('utf8');
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    /**
     * Prepare and execute a safe query
     * @param string $sql SQL query
     * @param string|null $types Types string for bind_param (e.g., 'ss', 'i')
     * @param array $params Array of parameters to bind
     * @return mysqli_result|bool|mysqli_stmt
     */
    public function query($sql, $types = null, $params = []) {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }

        if ($types && !empty($params)) {
             // bind_param needs references, so we need to do some magic if we want to wrap it perfectly,
             // but for now let's just use the spread operator with references if possible or keep it simple.
             // Actually, simple spread works in newer PHP but bind_param is picky.
             // Let's use the explicit bind_param call usage pattern in the calling code being safer OR implement a dynamic binder.
             $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        
        // Return statement if it's a SELECT so we can get_result(), otherwise return true/false?
        // Actually, let's usually return the result for SELECTs, and the stmt for flexibility?
        // Let's make it standard: return the statement. The caller can do get_result() or affected_rows.
        return $stmt;
    }
    
    // Helper for fetching all rows as associative array
    public function fetchAll($sql, $types = null, $params = []) {
        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    // Helper for fetching a single row
    public function fetchOne($sql, $types = null, $params = []) {
        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }
    
    // Prevent cloning
    private function __clone() {}
}
?>
