<?php
require_once __DIR__ . '/Model.php';

class Usuario extends Model {
    protected $table = 'Usuario';

    public function __construct() {
        parent::__construct();
    }

    public function login($email, $senha) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            unset($usuario['senha']); // Remove a senha do retorno
            return $usuario;
        }
        return false;
    }

    public function create($data) {
        // Hash da senha antes de salvar
        if (isset($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }

    public function update($id, $data) {
        // Hash da senha se estiver sendo atualizada
        if (isset($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        return parent::update($id, $data);
    }

    public function getByEmail($email) {
        return $this->findBy('email', $email);
    }

    public function getByTipo($tipo) {
        return $this->findBy('tipo', $tipo);
    }
} 