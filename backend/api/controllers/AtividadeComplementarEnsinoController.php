<?php
require_once __DIR__ . '/../models/AtividadeComplementarEnsino.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/Database.php';

use backend\api\models\AtividadeComplementarEnsino;
use backend\api\middleware\AuthMiddleware;
use backend\api\config\Database;
use Exception;

class AtividadeComplementarEnsinoController {
    
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
            if (empty($input['tipo_atividade'])) {
                error_log("Erro: Tipo de atividade não informado");
                http_response_code(400);
                echo json_encode(['erro' => 'Tipo de atividade é obrigatório']);
                return;
            }
            
            error_log("Tipo de atividade recebido: " . $input['tipo_atividade']);

            // Adicionar o ID do aluno logado
            $input['aluno_id'] = $usuario['id'];

            // Validar campos específicos baseado na categoria
            $tipo_atividade = $input['tipo_atividade'] ?? '';
            $input['categoria_id'] = 1; // Garantir categoria de Ensino
            
            // Garantir que atividade_disponivel_id está presente (obrigatório no banco)
            if (empty($input['atividade_disponivel_id'])) {
                // Primeiro tentar buscar na tabela atividadesdisponiveisbcc23
                try {
                    $db = Database::getInstance()->getConnection();
                    $sql = "SELECT id FROM atividadesdisponiveisbcc23 WHERE categoria_id = 1 LIMIT 1";
                    $stmt = $db->prepare($sql);
                    
                    if ($stmt && $stmt->execute()) {
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $input['atividade_disponivel_id'] = $row['id'];
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Erro ao buscar em atividadesdisponiveisbcc23: " . $e->getMessage());
                }
                
                // Se ainda não encontrou, usar fallback baseado no tipo
                if (empty($input['atividade_disponivel_id'])) {
                    $tipo_atividade = $input['tipo_atividade'] ?? '';
                    if ($tipo_atividade === 'Outras IES') {
                        $input['atividade_disponivel_id'] = 1; // Disciplinas em outras IES
                    } elseif ($tipo_atividade === 'UFOPA') {
                        $input['atividade_disponivel_id'] = 2; // Disciplinas na UFOPA
                    } elseif ($tipo_atividade === 'Monitoria') {
                        $input['atividade_disponivel_id'] = 3; // Monitoria
                    } else {
                        $input['atividade_disponivel_id'] = 2; // Padrão: UFOPA
                    }
                }
                error_log("atividade_disponivel_id não fornecido, usando valor baseado no tipo: " . $input['atividade_disponivel_id']);
            }
            
            error_log("atividade_disponivel_id definido como: " . $input['atividade_disponivel_id']);
            

            // Validar campos específicos baseado no tipo de atividade
if ($tipo_atividade === 'Outras IES') {
    if (empty($input['nome_disciplina']) || empty($input['nome_instituicao']) || empty($input['carga_horaria'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Para disciplinas em outras IES são obrigatórios: nome da disciplina, instituição e carga horária']);
        return;
    }
} elseif ($tipo_atividade === 'UFOPA') {
    if (empty($input['nome_disciplina']) || empty($input['carga_horaria'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Para disciplinas na UFOPA são obrigatórios: nome da disciplina e carga horária']);
        return;
    }
} elseif ($tipo_atividade === 'Monitoria') {
    if (empty($input['nome_disciplina_laboratorio']) || empty($input['data_inicio']) || empty($input['data_fim'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Para monitoria são obrigatórios: nome da disciplina/laboratório, data de início e data de fim']);
        return;
    }
} else {
    http_response_code(400);
    echo json_encode(['erro' => 'Tipo de atividade inválido']);
    return;
}

            // Processar upload de arquivo se enviado
            $fileField = '';
            if ($tipo_atividade === 'Outras IES') {
                $fileField = 'declaracao_ies';
            } elseif ($tipo_atividade === 'UFOPA') {
                $fileField = 'comprovante_ufopa';
            } elseif ($tipo_atividade === 'Monitoria') {
                $fileField = 'comprovante';
            }

            if (!empty($fileField) && isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/declaracoes/';
                
                // Criar diretório se não existir
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES[$fileField]['name']);
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $uploadPath)) {
                    $input['declaracao_caminho'] = 'uploads/declaracoes/' . $fileName;
                }
            }

            // Criar a atividade
            $atividade_id = AtividadeComplementarEnsino::create($input);

            http_response_code(201);
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Atividade complementar de ensino cadastrada com sucesso',
                'atividade_id' => $atividade_id
            ]);

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEnsinoController::criar: " . $e->getMessage());
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
                $atividades = AtividadeComplementarEnsino::buscarPorAluno($usuario['id']);
            } else {
                // Para coordenadores, implementar lógica específica se necessário
                $aluno_id = $_GET['aluno_id'] ?? null;
                if (!$aluno_id) {
                    http_response_code(400);
                    echo json_encode(['erro' => 'ID do aluno é obrigatório']);
                    return;
                }
                $atividades = AtividadeComplementarEnsino::buscarPorAluno($aluno_id);
            }

            echo json_encode([
                'success' => true,
                'data' => $atividades
            ]);

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEnsinoController::listar: " . $e->getMessage());
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

            $atividades = AtividadeComplementarEnsino::buscarPorAluno($aluno_id);

            echo json_encode([
                'success' => true,
                'data' => $atividades
            ]);

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEnsinoController::listarPorAluno: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno do servidor']);
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

            $atividade = AtividadeComplementarEnsino::buscarPorId($id);
            
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
                'sucesso' => true,
                'atividade' => $atividade
            ]);

        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEnsinoController::buscarPorId: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor']);
        }
    }
}