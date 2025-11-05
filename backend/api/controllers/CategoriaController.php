<?php
require_once __DIR__ . '/../models/CategoriaModel.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/JWTService.php';

use backend\api\config\Database;
use backend\api\services\JWTService;
use backend\api\models\CategoriaModel;

class CategoriaController {
    
    /**
     * Obter dados da requisição
     * @return array|null
     */
    protected function getRequestData() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
    
    /**
     * Enviar resposta JSON
     * @param array $data Dados para resposta
     * @param int $statusCode Código de status HTTP
     */
    protected function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    /**
     * Listar todas as categorias de atividades
     */
    public function listarTodas() {
        try {
            // Buscar categorias no model
            $categorias = CategoriaModel::listarTodas();
            
            // Verificar se encontrou categorias
            if (empty($categorias)) {
                $this->sendJsonResponse([
                    'success' => false,
                    'message' => 'Nenhuma categoria encontrada',
                    'data' => []
                ], 404);
                return;
            }
            
            // Resposta de sucesso
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Categorias listadas com sucesso',
                'data' => $categorias,
                'total' => count($categorias)
            ]);
            
        } catch (Exception $e) {
            error_log("Erro em CategoriaController::listarTodas: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro interno do servidor ao listar categorias'
            ], 500);
        }
    }
    
    /**
     * Buscar categoria por ID
     * @param int $id ID da categoria
     */
    public function buscarPorId($id) {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                $this->sendJsonResponse([
                    'success' => false,
                    'error' => 'ID da categoria inválido'
                ], 400);
                return;
            }
            
            // Buscar categoria no model
            $categoria = CategoriaModel::buscarPorId($id);
            
            // Verificar se encontrou a categoria
            if (!$categoria) {
                $this->sendJsonResponse([
                    'success' => false,
                    'message' => 'Categoria não encontrada'
                ], 404);
                return;
            }
            
            // Resposta de sucesso
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Categoria encontrada com sucesso',
                'data' => $categoria
            ]);
            
        } catch (Exception $e) {
            error_log("Erro em CategoriaController::buscarPorId: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro interno do servidor ao buscar categoria'
            ], 500);
        }
    }
    
    /**
     * Verificar se uma categoria existe
     * @param int $id ID da categoria
     */
    public function verificarExistencia($id) {
        try {
            // Validar ID
            if (!is_numeric($id) || $id <= 0) {
                $this->sendJsonResponse([
                    'success' => false,
                    'error' => 'ID da categoria inválido'
                ], 400);
                return;
            }
            
            // Verificar existência no model
            $existe = CategoriaModel::existe($id);
            
            // Resposta
            $this->sendJsonResponse([
                'success' => true,
                'existe' => $existe,
                'message' => $existe ? 'Categoria existe' : 'Categoria não existe'
            ]);
            
        } catch (Exception $e) {
            error_log("Erro em CategoriaController::verificarExistencia: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro interno do servidor ao verificar categoria'
            ], 500);
        }
    }
}
?>