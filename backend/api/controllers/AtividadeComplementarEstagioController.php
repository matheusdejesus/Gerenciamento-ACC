<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../models/AtividadeComplementarEstagio.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use backend\api\models\AtividadeComplementarEstagio;
use backend\api\middleware\AuthMiddleware;
use Exception;

class AtividadeComplementarEstagioController {
    
    public function criar() {
        try {
            // Verificar autenticação
            $usuario = AuthMiddleware::validateToken();
            
            if (!$usuario || $usuario['tipo'] !== 'aluno') {
                http_response_code(403);
                echo json_encode(['erro' => 'Acesso negado. Apenas alunos podem cadastrar atividades.']);
                return;
            }

            // Verificar se é POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['erro' => 'Método não permitido']);
                return;
            }

            // Obter dados do POST
            $input = $_POST;
            
            // Log para debug
            error_log("Dados recebidos no POST: " . print_r($input, true));
            error_log("Arquivos recebidos: " . print_r($_FILES, true));
            
            if (empty($input)) {
                error_log("Erro: Dados POST vazios");
                http_response_code(400);
                echo json_encode(['erro' => 'Dados inválidos - nenhum dado foi enviado']);
                return;
            }

            // Validar dados obrigatórios
            $camposObrigatorios = ['empresa', 'area', 'data_inicio', 'data_fim', 'horas'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($input[$campo])) {
                    error_log("Erro: Campo obrigatório não informado: " . $campo);
                    http_response_code(400);
                    echo json_encode(['erro' => "Campo '{$campo}' é obrigatório"]);
                    return;
                }
            }
            
            // Adicionar o ID do aluno logado
            $input['aluno_id'] = $usuario['id'];
            $input['status'] = 'Aguardando avaliação';
            $input['data_submissao'] = date('Y-m-d H:i:s');

            // Processar upload de arquivo se enviado
            if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/declaracoes/';
                
                // Criar diretório se não existir
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['declaracao']['name']);
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['declaracao']['tmp_name'], $uploadPath)) {
                    $input['declaracao_caminho'] = 'uploads/declaracoes/' . $fileName;
                } else {
                    error_log("Erro ao fazer upload do arquivo");
                    http_response_code(500);
                    echo json_encode(['erro' => 'Erro ao fazer upload do arquivo']);
                    return;
                }
            }

            // Criar a atividade
            $atividade_id = AtividadeComplementarEstagio::create($input);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Atividade de estágio cadastrada com sucesso',
                'atividade_id' => $atividade_id
            ]);

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEstagioController::criar: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
        }
    }
    
    public function listar() {
        try {
            // Verificar autenticação
            $usuario = AuthMiddleware::validateToken();
            
            if (!$usuario) {
                http_response_code(403);
                echo json_encode(['erro' => 'Acesso negado']);
                return;
            }

            // Se for aluno, listar apenas suas atividades
            if ($usuario['tipo'] === 'aluno') {
                $atividades = AtividadeComplementarEstagio::buscarPorAluno($usuario['id']);
            } else {
                // Para coordenadores, implementar lógica específica se necessário
                $aluno_id = $_GET['aluno_id'] ?? null;
                if (!$aluno_id) {
                    http_response_code(400);
                    echo json_encode(['erro' => 'ID do aluno é obrigatório']);
                    return;
                }
                $atividades = AtividadeComplementarEstagio::buscarPorAluno($aluno_id);
            }

            echo json_encode([
                'success' => true,
                'atividades' => $atividades
            ]);

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEstagioController::listar: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor']);
        }
    }
    
    public function buscarPorId($id) {
        try {
            // Verificar autenticação
            $usuario = AuthMiddleware::validateToken();
            
            if (!$usuario) {
                http_response_code(403);
                echo json_encode(['erro' => 'Acesso negado']);
                return;
            }

            $atividade = AtividadeComplementarEstagio::buscarPorId($id);
            
            if (!$atividade) {
                http_response_code(404);
                echo json_encode(['erro' => 'Atividade não encontrada']);
                return;
            }
            
            // Verificar se o aluno pode acessar esta atividade
            if ($usuario['tipo'] === 'aluno' && $atividade['aluno_id'] !== $usuario['id']) {
                http_response_code(403);
                echo json_encode(['erro' => 'Acesso negado a esta atividade']);
                return;
            }

            echo json_encode([
                'success' => true,
                'atividade' => $atividade
            ]);

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEstagioController::buscarPorId: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor']);
        }
    }
    
    public function listarPorAluno() {
        try {
            $aluno_id = $_GET['aluno_id'] ?? null;
            
            if (empty($aluno_id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do aluno é obrigatório']);
                return;
            }

            $atividades = AtividadeComplementarEstagio::buscarPorAluno($aluno_id);

            echo json_encode([
                'success' => true,
                'data' => $atividades
            ]);

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEstagioController::listarPorAluno: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno do servidor']);
        }
    }
    
    public function atualizarStatus() {
        try {
            // Verificar autenticação
            $usuario = AuthMiddleware::validateToken();
            
            if (!$usuario || $usuario['tipo'] !== 'coordenador') {
                http_response_code(403);
                echo json_encode(['erro' => 'Acesso negado. Apenas coordenadores podem atualizar status.']);
                return;
            }

            // Verificar se é PUT
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                http_response_code(405);
                echo json_encode(['erro' => 'Método não permitido']);
                return;
            }

            // Obter dados do PUT
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id']) || empty($input['status'])) {
                http_response_code(400);
                echo json_encode(['erro' => 'ID da atividade e status são obrigatórios']);
                return;
            }

            $dados = [
                'status' => $input['status'],
                'data_avaliacao' => date('Y-m-d H:i:s'),
                'avaliador_id' => $usuario['id']
            ];
            
            if (!empty($input['observacoes_avaliacao'])) {
                $dados['observacoes_avaliacao'] = $input['observacoes_avaliacao'];
            }

            $sucesso = AtividadeComplementarEstagio::atualizarStatus(
                $input['id'], 
                $input['status'], 
                $usuario['id'], 
                $input['observacoes_avaliacao'] ?? null
            );
            
            if ($sucesso) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Status da atividade atualizado com sucesso'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Atividade não encontrada']);
            }

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEstagioController::atualizarStatus: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor']);
        }
    }
}
?>