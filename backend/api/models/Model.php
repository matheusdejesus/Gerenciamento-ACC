<?php
require_once __DIR__ . '/../config/database.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll() {
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        $types = str_repeat('s', count($fields));
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    public function update($id, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        
        $set = implode('=?,', $fields) . '=?';
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db->prepare($sql);
        $values[] = $id;
        
        $types = str_repeat('s', count($fields)) . 'i';
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function findBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE $field = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $value);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
} 