<?php
namespace backend\api\models;

use backend\api\config\Database;

class RecuperarSenha {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function criarToken($usuario_id, $token) {
        $stmt = $this->db->prepare("INSERT INTO recuperarsenha (usuario_id, token, criacao) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $usuario_id, $token);
        return $stmt->execute();
    }
    
    public function validarToken($token) {
        $stmt = $this->db->prepare("SELECT usuario_id FROM recuperarsenha WHERE token = ? AND criacao > DATE_SUB(NOW(), INTERVAL 20 MINUTE)");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function removerToken($token) {
        $stmt = $this->db->prepare("DELETE FROM recuperarsenha WHERE token = ?");
        $stmt->bind_param("s", $token);
        return $stmt->execute();
    }
    
    public function limparTokensExpirados() {
        $stmt = $this->db->prepare("DELETE FROM recuperarsenha WHERE criacao < DATE_SUB(NOW(), INTERVAL 20 MINUTE)");
        return $stmt->execute();
    }
}
?>