<?php
namespace backend\api\middleware;

require_once __DIR__ . '/../controllers/LogAcoesController.php';

use backend\api\controllers\LogAcoesController;

class AuditoriaMiddleware {


    // Registrar ação automaticamente

    public static function registrarAcao($usuario_id, $acao, $descricao = null) {
        try {
            // Adicionar informações extras da requisição
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $metodo = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
            $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
            
            $descricaoCompleta = $descricao;
            if ($descricao) {
                $descricaoCompleta .= " | IP: {$ip} | Método: {$metodo} | URI: {$uri}";
            }
            
            return LogAcoesController::registrar($usuario_id, $acao, $descricaoCompleta);
            
        } catch (\Exception $e) {
            error_log("Erro no AuditoriaMiddleware: " . $e->getMessage());
            return false;
        }
    }
    
    
    // Interceptar e registrar ações de uma rota específica
     
    public static function interceptarRota($usuario, $acao, $descricao = null) {
        if ($usuario && isset($usuario['id'])) {
            return self::registrarAcao($usuario['id'], $acao, $descricao);
        }
        return false;
    }
}
?>