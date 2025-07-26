<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Controller.php';

use backend\api\config\Database;
use backend\api\controllers\Controller;
use Exception;

class LogAcoesController extends Controller {
    
    // Método para listar todos os logs
    public function buscarTodos() {
        try {
            $logs = self::listarTodos();
            $this->sendJsonResponse([
                'success' => true,
                'data' => $logs
            ]);
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::buscarTodos: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro ao buscar logs'
            ], 500);
        }
    }
    
    // Método para buscar logs por usuário
    public function buscarPorUsuario($usuario_id) {
        try {
            $logs = self::listarPorUsuario($usuario_id);
            $this->sendJsonResponse([
                'success' => true,
                'data' => $logs
            ]);
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::buscarPorUsuario: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro ao buscar logs do usuário'
            ], 500);
        }
    }
    
    // Registrar uma ação no log
    public static function registrar($usuario_id, $acao, $descricao = null) {
        try {
            require_once __DIR__ . '/../models/LogAcoes.php';
            return \backend\api\models\LogAcoes::registrarAcao($usuario_id, $acao, $descricao);
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::registrar: " . $e->getMessage());
            return false;
        }
    }
    
    
    // Listar logs de um usuário
    public static function listarPorUsuario($usuario_id, $limite = 50) {
        try {
            require_once __DIR__ . '/../models/LogAcoes.php';
            return \backend\api\models\LogAcoes::buscarPorUsuario($usuario_id, $limite);
        } catch (Exception $e) {
            error_log("Erro em LogAcoesController::listarPorUsuario: " . $e->getMessage());
            return [];
        }
    }
    
    
    // Listar todos os logs (para administradores)
    public static function listarTodos($limite = 100) {
        try {
            // Delegar para o model
            require_once __DIR__ . '/../models/LogAcoes.php';
            return \backend\api\models\LogAcoes::buscarTodos([], $limite, 0);
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
    
    // Limpar logs antigos
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