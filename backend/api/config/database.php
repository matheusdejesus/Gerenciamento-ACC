<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($this->connection->connect_error) {
                throw new Exception("Falha na conexão: " . $this->connection->connect_error);
            }
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
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
        try {
            $result = $this->connection->query($sql);
            if ($result === false) {
                throw new Exception("Erro na query: " . $this->connection->error);
            }
            return $result;
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function prepare($sql) {
        try {
            $stmt = $this->connection->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Erro no prepare: " . $this->connection->error);
            }
            return $stmt;
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
} 