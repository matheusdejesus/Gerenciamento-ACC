<?php
namespace backend\api\config;

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = 'localhost';
        $username = 'root';
        $password = '';
        $database = 'acc';
        
        $this->connection = new \mysqli($host, $username, $password, $database);
        
        if ($this->connection->connect_error) {
            throw new \Exception("Erro de conexão: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset("utf8mb4");
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
}
?>