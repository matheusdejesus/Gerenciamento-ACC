<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Controller.php';

use backend\api\config\Database;
use backend\api\controllers\Controller;
use Exception;

class LogAcoesController extends Controller {
    
    
    // Registrar uma ação no log

    public static function registrar($usuario_id, $acao, $descricao = null) {
        try {
            error_log("=== LogAcoesController::registrar ===");
            error_log("Usuario ID: " . $usuario_id);
            error_log("Ação: " . $acao);
            error_log("Descrição: " . $descricao);
            
            $db = Database::getInstance()->getConnection();
            
            $sql = "INSERT INTO LogAcoes (usuario_id, acao, descricao, data_hora) VALUES (?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                error_log("Erro ao preparar statement: " . $db->error);
                return false;
            }
            
            $stmt->bind_param("iss", $usuario_id, $acao, $descricao);
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $log_id = $db->insert_id;
                error_log("Log registrado com sucesso - ID: " . $log_id);
                $stmt->close();
                return true;
            } else {
                error_log("Erro ao executar statement: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::registrar: " . $e->getMessage());
            return false;
        }
    }
    
    
    // Listar logs de um usuário
    
    public static function listarPorUsuario($usuario_id, $limite = 50) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT l.*, u.nome as nome_usuario 
                    FROM LogAcoes l 
                    JOIN Usuario u ON l.usuario_id = u.id 
                    WHERE l.usuario_id = ? 
                    ORDER BY l.data_hora DESC 
                    LIMIT ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ii", $usuario_id, $limite);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $logs = [];
            
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
            
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::listarPorUsuario: " . $e->getMessage());
            return [];
        }
    }
    
    
    // Listar todos os logs (para administradores)
    
    public static function listarTodos($limite = 100) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT l.*, u.nome as nome_usuario 
                    FROM LogAcoes l 
                    JOIN Usuario u ON l.usuario_id = u.id 
                    ORDER BY l.data_hora DESC 
                    LIMIT ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $limite);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $logs = [];
            
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
            
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::listarTodos: " . $e->getMessage());
            return [];
        }
    }
    
    
    // Buscar logs por ação específica
    
    public static function buscarPorAcao($acao, $limite = 50) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT l.*, u.nome as nome_usuario 
                    FROM LogAcoes l 
                    JOIN Usuario u ON l.usuario_id = u.id 
                    WHERE l.acao = ? 
                    ORDER BY l.data_hora DESC 
                    LIMIT ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("si", $acao, $limite);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $logs = [];
            
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
            
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::buscarPorAcao: " . $e->getMessage());
            return [];
        }
    }
    
    
    // Limpar logs antigos (mais de X dias)
    
    public static function limparLogsAntigos($dias = 90) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "DELETE FROM LogAcoes WHERE data_hora < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $dias);
            
            $resultado = $stmt->execute();
            $linhas_afetadas = $stmt->affected_rows;
            
            $stmt->close();
            
            error_log("Logs antigos removidos: " . $linhas_afetadas . " registros");
            return $linhas_afetadas;
            
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::limparLogsAntigos: " . $e->getMessage());
            return 0;
        }
    }
}
?>