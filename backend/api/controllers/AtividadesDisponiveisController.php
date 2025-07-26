<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
require_once __DIR__ . '/../models/AtividadeComplementar.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/LogAcoesController.php';

use backend\api\models\AtividadesDisponiveis;
use backend\api\models\AtividadeComplementar;
use Exception;

class AtividadesDisponiveisController {
    
    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    private function validateRequiredFields($data, $fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Campo obrigatório ausente: $field");
            }
        }
    }

    public function listar() {
        try {
            $atividades = AtividadesDisponiveis::listarTodas();
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveisController::listar: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro ao buscar atividades: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarPorId() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                $this->sendJsonResponse(['success' => false, 'error' => 'ID é obrigatório'], 400);
                return;
            }

            $atividade = AtividadesDisponiveis::buscarPorId($id);
            if (!$atividade) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Atividade não encontrada'], 404);
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
                'error' => 'Erro ao buscar atividade: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editar() {
        try {
            // Validar autenticação JWT
            $usuario = \backend\api\middleware\AuthMiddleware::validateToken();
            if (!$usuario || $usuario['tipo'] !== 'admin') {
                $this->sendJsonResponse(['success' => false, 'error' => 'Acesso negado'], 403);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Dados inválidos'], 400);
                return;
            }

            $this->validateRequiredFields($data, ['id', 'titulo', 'descricao', 'categoria_id', 'carga_horaria']);

            // Buscar dados atuais para auditoria
            $atividadeAtual = AtividadesDisponiveis::buscarPorId($data['id']);
            if (!$atividadeAtual) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Atividade não encontrada'], 404);
                return;
            }

            // Buscar nome da nova categoria se houver mudança
            $novaCategoriaNome = null;
            if ($atividadeAtual['categoria_id'] != $data['categoria_id']) {
                try {
                    $categorias = AtividadeComplementar::listarCategorias();
                    foreach ($categorias as $cat) {
                        if ($cat['id'] == $data['categoria_id']) {
                            $novaCategoriaNome = $cat['nome'];
                            break;
                        }
                    }
                } catch (Exception $e) {
                    error_log("Erro ao buscar categorias: " . $e->getMessage());
                }
            }

            $sucesso = AtividadesDisponiveis::editar(
                $data['id'],
                $data['titulo'],
                $data['descricao'],
                $data['categoria_id'],
                $data['carga_horaria']
            );

            if ($sucesso) {
                $alteracoes = [];
                
                if ($atividadeAtual['titulo'] !== $data['titulo']) {
                    $alteracoes[] = "Título: '{$atividadeAtual['titulo']}' → '{$data['titulo']}'";
                }
                
                if ($atividadeAtual['descricao'] !== $data['descricao']) {
                    $alteracoes[] = "Descrição alterada";
                }
                
                if ($atividadeAtual['categoria_id'] != $data['categoria_id']) {
                    $categoriaAnterior = $atividadeAtual['categoria_nome'] ?? "ID {$atividadeAtual['categoria_id']}";
                    $categoriaNova = $novaCategoriaNome ?? "ID {$data['categoria_id']}";
                    $alteracoes[] = "Categoria: '{$categoriaAnterior}' → '{$categoriaNova}'";
                }
                
                if ($atividadeAtual['carga_horaria'] != $data['carga_horaria']) {
                    $alteracoes[] = "Carga Horária: {$atividadeAtual['carga_horaria']}h → {$data['carga_horaria']}h";
                }

                $descricaoAuditoria = empty($alteracoes) 
                    ? "Admin acessou atividade '{$atividadeAtual['titulo']}' (ID {$data['id']}) sem alterações" 
                    : "Admin alterou atividade '{$atividadeAtual['titulo']}' (ID {$data['id']}): " . implode('; ', $alteracoes);
                
                // Registrar auditoria
                LogAcoesController::registrar(
                    $usuario['id'],
                    'ALTERAR_ATIVIDADE',
                    $descricaoAuditoria
                );
                
                $this->sendJsonResponse(['success' => true, 'message' => 'Atividade atualizada com sucesso']);
            } else {
                $this->sendJsonResponse(['success' => false, 'error' => 'Falha ao atualizar atividade'], 500);
            }
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveisController::editar: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function remover() {
        try {
            error_log("=== AtividadesDisponiveisController::remover INICIADO ===");
            
            // Validar autenticação JWT
            $usuario = \backend\api\middleware\AuthMiddleware::validateToken();
            if (!$usuario || $usuario['tipo'] !== 'admin') {
                error_log("Usuário não autorizado: " . json_encode($usuario));
                $this->sendJsonResponse(['success' => false, 'error' => 'Acesso negado'], 403);
                return;
            }

            // Obter ID da atividade
            $atividade_id = null;
            
            if (isset($_GET['id'])) {
                $atividade_id = intval($_GET['id']);
                error_log("ID da atividade via GET: " . $atividade_id);
            } else {
                $data = json_decode(file_get_contents('php://input'), true);
                $atividade_id = $data['id'] ?? null;
                error_log("ID da atividade via POST: " . $atividade_id);
                error_log("Dados recebidos: " . json_encode($data));
            }

            if (!$atividade_id || !is_numeric($atividade_id)) {
                error_log("ID da atividade inválido: " . $atividade_id);
                $this->sendJsonResponse(['success' => false, 'error' => 'ID da atividade é obrigatório'], 400);
                return;
            }

            // Buscar dados da atividade antes de remover
            $atividadeAtual = AtividadesDisponiveis::buscarPorId($atividade_id);
            if (!$atividadeAtual) {
                error_log("Atividade não encontrada: " . $atividade_id);
                $this->sendJsonResponse(['success' => false, 'error' => 'Atividade não encontrada'], 404);
                return;
            }

            error_log("Atividade encontrada: " . json_encode($atividadeAtual));

            // Verificar se existem atividades complementares vinculadas
            require_once __DIR__ . '/../models/AtividadeComplementar.php';
            $atividadesVinculadas = \backend\api\models\AtividadeComplementar::contarPorAtividadeDisponivel($atividade_id);
            
            if ($atividadesVinculadas > 0) {
                error_log("Atividades vinculadas encontradas: " . $atividadesVinculadas);
        
                $sucessoRemocaoVinculadas = \backend\api\models\AtividadeComplementar::removerPorAtividadeDisponivel($atividade_id);
                
                if (!$sucessoRemocaoVinculadas) {
                    error_log("Falha ao remover atividades complementares vinculadas");
                    $this->sendJsonResponse([
                        'success' => false, 
                        'error' => 'Erro ao remover atividades complementares vinculadas'
                    ], 500);
                    return;
                }
                
                error_log("Atividades complementares vinculadas removidas com sucesso");
            }

            // Remover a atividade
            $sucesso = AtividadesDisponiveis::remover($atividade_id);
            error_log("Resultado da remoção: " . ($sucesso ? 'sucesso' : 'falha'));

            if ($sucesso) {
                // Registrar auditoria
                require_once __DIR__ . '/LogAcoesController.php';
                LogAcoesController::registrar(
                    $usuario['id'],
                    'REMOVER_ATIVIDADE',
                    "Atividade removida: '{$atividadeAtual['titulo']}' (ID: {$atividade_id}) - Categoria: {$atividadeAtual['categoria_nome']}"
                );
                
                error_log("Auditoria registrada com sucesso");
                
                $this->sendJsonResponse([
                    'success' => true, 
                    'message' => 'Atividade removida com sucesso'
                ]);
            } else {
                error_log("Falha ao remover atividade do banco de dados");
                $this->sendJsonResponse([
                    'success' => false, 
                    'error' => 'Falha ao remover atividade'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveisController::remover: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendJsonResponse([
                'success' => false, 
                'error' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adicionar() {
        try {
            // Validar autenticação JWT
            $usuario = \backend\api\middleware\AuthMiddleware::validateToken();
            if (!$usuario || $usuario['tipo'] !== 'admin') {
                $this->sendJsonResponse(['success' => false, 'error' => 'Acesso negado'], 403);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Dados inválidos'], 400);
                return;
            }

            $this->validateRequiredFields($data, ['titulo', 'descricao', 'categoria_id', 'carga_horaria']);

            // Chama o model para inserir
            $id = \backend\api\models\AtividadesDisponiveis::adicionar(
                $data['titulo'],
                $data['descricao'],
                $data['categoria_id'],
                $data['carga_horaria']
            );

            if ($id) {
                // Registrar auditoria
                LogAcoesController::registrar(
                    $usuario['id'],
                    'ADICIONAR_ATIVIDADE',
                    "Atividade adicionada: '{$data['titulo']}' (ID: {$id}) - Categoria ID: {$data['categoria_id']}"
                );
                
                $this->sendJsonResponse(['success' => true, 'id' => $id, 'message' => 'Atividade adicionada com sucesso']);
            } else {
                $this->sendJsonResponse(['success' => false, 'error' => 'Falha ao adicionar atividade'], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadesDisponiveisController::adicionar: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function listarCategorias() {
        try {
            $categorias = \backend\api\models\AtividadeComplementar::listarCategorias();
            $this->sendJsonResponse(['success' => true, 'data' => $categorias]);
        } catch (\Exception $e) {
            error_log("Erro ao listar categorias: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'error' => 'Erro ao carregar categorias'], 500);
        }
    }
}
?>