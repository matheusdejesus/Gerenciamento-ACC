<?php
namespace backend\api\controllers;

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/AtividadesDisponiveis.php';

use backend\api\models\AtividadesDisponiveis;

class AtividadesDisponiveisController extends Controller {
    
    public function listar() {
        try {
            $atividades = AtividadesDisponiveis::listarTodas();
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades,
                'total' => count($atividades)
            ]);
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveisController::listar: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro ao buscar atividades disponíveis'
            ], 500);
        }
    }
    
    public function buscarPorId() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id || !is_numeric($id)) {
                $this->sendJsonResponse([
                    'success' => false,
                    'error' => 'ID da atividade é obrigatório'
                ], 400);
                return;
            }
            
            $atividade = AtividadesDisponiveis::buscarPorId((int)$id);
            
            if (!$atividade) {
                $this->sendJsonResponse([
                    'success' => false,
                    'error' => 'Atividade não encontrada'
                ], 404);
                return;
            }
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividade
            ]);
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveisController::buscarPorId: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro ao buscar atividade'
            ], 500);
        }
    }
    
    public function buscarPorCategoria() {
        try {
            $categoria = $_GET['categoria'] ?? '';
            
            if (empty($categoria)) {
                $this->sendJsonResponse([
                    'success' => false,
                    'error' => 'Categoria é obrigatória'
                ], 400);
                return;
            }
            
            $atividades = AtividadesDisponiveis::buscarPorCategoria($categoria);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades,
                'total' => count($atividades)
            ]);
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveisController::buscarPorCategoria: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro ao buscar atividades por categoria'
            ], 500);
        }
    }
}
?>