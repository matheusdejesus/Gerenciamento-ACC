<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../models/AvaliarAtividadeModel.php';
require_once __DIR__ . '/Controller.php';

use backend\api\models\AvaliarAtividadeModel;
use Exception;

class AvaliarAtividadeController extends Controller {
    
    /**
     * Listar certificados processados (aprovados/rejeitados)
     */
    public function listarCertificadosProcessados($usuarioLogado) {
        try {
            error_log("[DEBUG] Listando certificados processados para coordenador: " . $usuarioLogado['id']);
            
            $certificados = AvaliarAtividadeModel::listarCertificadosProcessados($usuarioLogado['id']);
            
            $this->enviarSucesso($certificados, 'Certificados processados carregados com sucesso');
            
        } catch (Exception $e) {
            error_log("[ERROR] Erro ao listar certificados processados: " . $e->getMessage());
            $this->enviarErro('Erro ao carregar certificados processados: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Aprovar certificado
     */
    public function aprovarCertificado($atividadeId, $observacoes, $usuarioLogado, $chAtribuida) {
        try {
            error_log("[DEBUG] Aprovando certificado ID: $atividadeId pelo coordenador: " . $usuarioLogado['id'] . " com carga horária: $chAtribuida");
            
            $resultado = AvaliarAtividadeModel::aprovarCertificado($atividadeId, $observacoes, $usuarioLogado['id'], $chAtribuida);
            
            if ($resultado) {
                $this->enviarSucesso(null, "Certificado aprovado com sucesso com {$chAtribuida}h atribuídas");
            } else {
                $this->enviarErro('Erro ao aprovar certificado', 500);
            }
            
        } catch (Exception $e) {
            error_log("[ERROR] Erro ao aprovar certificado: " . $e->getMessage());
            $this->enviarErro('Erro ao aprovar certificado: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Rejeitar certificado
     */
    public function rejeitarCertificado($atividadeId, $observacoes, $usuarioLogado) {
        try {
            error_log("[DEBUG] Rejeitando certificado ID: $atividadeId pelo coordenador: " . $usuarioLogado['id']);
            
            if (empty(trim($observacoes))) {
                $this->enviarErro('Observações são obrigatórias para rejeição', 400);
                return;
            }
            
            $resultado = AvaliarAtividadeModel::rejeitarCertificado($atividadeId, $observacoes, $usuarioLogado['id']);
            
            if ($resultado) {
                $this->enviarSucesso(null, 'Certificado rejeitado com sucesso');
            } else {
                $this->enviarErro('Erro ao rejeitar certificado', 500);
            }
            
        } catch (Exception $e) {
            error_log("[ERROR] Erro ao rejeitar certificado: " . $e->getMessage());
            $this->enviarErro('Erro ao rejeitar certificado: ' . $e->getMessage(), 500);
        }
    }
}
?>