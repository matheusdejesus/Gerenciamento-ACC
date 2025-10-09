<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../models/AtividadeComplementarEstagio.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/HorasLimiteService.php';

use backend\api\models\AtividadeComplementarEstagio;
use backend\api\middleware\AuthMiddleware;
use backend\api\services\HorasLimiteService;
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

            // VALIDAÇÃO CRÍTICA: Verificar se o aluno já atingiu o limite total de 240h
            $totalHorasAtual = HorasLimiteService::calcularTotalHorasAluno($usuario['id']);
            
            if ($totalHorasAtual >= 240) {
                error_log("ERRO: Limite de 240h atingido para aluno ID: " . $usuario['id']);
                http_response_code(400);
                echo json_encode(['erro' => '🚫 Limite total de 240 horas já foi atingido. Não é possível cadastrar novas atividades em nenhuma categoria.']);
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
            
            // Definir categoria_id como 4 para atividades de estágio
            $input['categoria_id'] = 4;
            
            // VALIDAÇÃO CRÍTICA: Verificar limite da categoria Estágio (100h)
            $horasAtualEstagio = HorasLimiteService::calcularHorasCategoria($usuario['id'], 'estagio');
            $limiteEstagio = HorasLimiteService::getLimiteCategoria('estagio');
            $horasSolicitadas = (int)$input['horas'];
            
            // Verificar se já atingiu o limite da categoria
            if ($horasAtualEstagio >= $limiteEstagio) {
                http_response_code(400);
                echo json_encode(['erro' => "🚫 Limite máximo de {$limiteEstagio} horas para atividades de Estágio já foi atingido. Você já possui {$horasAtualEstagio}h cadastradas nesta categoria."]);
                return;
            }
            
            // Verificar se a nova atividade excederia o limite da categoria
            $totalComNovaAtividade = $horasAtualEstagio + $horasSolicitadas;
            if ($totalComNovaAtividade > $limiteEstagio) {
                $horasRestantes = $limiteEstagio - $horasAtualEstagio;
                http_response_code(400);
                echo json_encode(['erro' => "⚠️ Limite da categoria Estágio seria excedido. Você possui {$horasAtualEstagio}h cadastradas e pode adicionar no máximo {$horasRestantes}h adicionais nesta categoria. Reduza as horas desta atividade para prosseguir."]);
                return;
            }

            // NOVA FUNCIONALIDADE: Validar limite de 100h para alunos 2017-2022
            $this->validarLimite100hCategoriaEstagio($usuario['id'], (int)$input['horas']);

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
    
    /**
     * NOVA FUNCIONALIDADE: Validar limite específico de 100h para categoria Estágio (alunos 2017-2022)
     */
    private function validarLimite100hCategoriaEstagio($aluno_id, $horas_solicitadas) {
        try {
            // Buscar matrícula do aluno
            require_once __DIR__ . '/../config/Database.php';
            $db = \backend\api\config\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT matricula FROM Aluno WHERE usuario_id = ?");
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $aluno_data = $result->fetch_assoc();
            
            if (!$aluno_data) {
                throw new Exception("Dados do aluno não encontrados");
            }
            
            $matricula = $aluno_data['matricula'];
            $anoMatricula = (int) substr($matricula, 0, 4);
            
            // Verificar se é aluno elegível para limite específico por categoria (matrículas 2017-2022)
            if ($anoMatricula >= 2017 && $anoMatricula <= 2022) {
                error_log("DEBUG LIMITE ESTAGIO 100H - Aluno elegível detectado. Matrícula: {$matricula}, Ano: {$anoMatricula}");
                
                // Calcular total de horas já utilizadas APENAS na categoria Estágio
                $horasEstagioAtual = $this->calcularHorasCategoriaEstagio($aluno_id);
                error_log("DEBUG LIMITE ESTAGIO 100H - Horas Estágio atuais: {$horasEstagioAtual}h");
                
                // Verificar se já atingiu o limite de 100h para Estágio
                if ($horasEstagioAtual >= 100) {
                    throw new Exception("🚫 Limite máximo de 100 horas atingido para atividades de Estágio. Você já possui {$horasEstagioAtual}h cadastradas nesta categoria. Não é possível cadastrar novas atividades de Estágio.");
                }
                
                // Verificar se a nova atividade excederia o limite de 100h para Estágio
                $totalComNovaAtividade = $horasEstagioAtual + $horas_solicitadas;
                if ($totalComNovaAtividade > 100) {
                    $horasRestantes = 100 - $horasEstagioAtual;
                    throw new Exception("⚠️ Limite de 100 horas para categoria Estágio seria excedido. Você possui {$horasEstagioAtual}h cadastradas em Estágio e pode adicionar no máximo {$horasRestantes}h adicionais nesta categoria. Reduza as horas desta atividade para prosseguir.");
                }
                
                error_log("DEBUG LIMITE ESTAGIO 100H - Validação aprovada. Total Estágio após cadastro: {$totalComNovaAtividade}h de 100h");
            }
            
        } catch (Exception $e) {
            error_log("Erro na validação de limite categoria Estágio: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * NOVA FUNCIONALIDADE: Calcular horas específicas da categoria Estágio para limite de 100h
     * Soma apenas horas da tabela atividadecomplementarestagio para alunos 2017-2022
     */
    private function calcularHorasCategoriaEstagio($aluno_id) {
        try {
            require_once __DIR__ . '/../config/Database.php';
            $db = \backend\api\config\Database::getInstance()->getConnection();
            
            $sql = "SELECT SUM(horas) as total_horas 
                    FROM atividadecomplementarestagio 
                    WHERE aluno_id = ? 
                    AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
                    
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $horasEstagio = $row && $row['total_horas'] ? (int) $row['total_horas'] : 0;
            error_log("DEBUG CALC ESTAGIO - Total Estágio para aluno {$aluno_id}: {$horasEstagio}h");
            
            return $horasEstagio;
            
        } catch (Exception $e) {
            error_log("Erro ao calcular horas categoria Estágio: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * NOVA FUNCIONALIDADE: Calcular total de horas extracurriculares para limite de 80h
     */
    private function calcularHorasExtracurricularesTotais($aluno_id) {
        try {
            require_once __DIR__ . '/../config/Database.php';
            $db = \backend\api\config\Database::getInstance()->getConnection();
            $horasTotal = 0;
            
            // Definir todas as tabelas de atividades extracurriculares e seus campos de horas
            $tabelas = [
                'atividadecomplementaracc' => 'horas_realizadas',
                'AtividadeComplementarEnsino' => 'carga_horaria', 
                'atividadecomplementarestagio' => 'horas',
                'atividadecomplementarpesquisa' => 'horas_realizadas',
                'AtividadeSocialComunitaria' => 'horas_realizadas'
            ];
            
            foreach ($tabelas as $tabela => $campoHoras) {
                $sql = "SELECT SUM({$campoHoras}) as total_horas 
                        FROM {$tabela} 
                        WHERE aluno_id = ? 
                        AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
                        
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $aluno_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                $horasTabela = $row && $row['total_horas'] ? (int) $row['total_horas'] : 0;
                error_log("DEBUG CALC 80H ESTAGIO - Tabela {$tabela}: {$horasTabela}h");
                
                $horasTotal += $horasTabela;
            }
            
            error_log("DEBUG CALC 80H ESTAGIO - Total calculado para aluno {$aluno_id}: {$horasTotal}h");
            return $horasTotal;
            
        } catch (Exception $e) {
            error_log("Erro ao calcular horas extracurriculares totais: " . $e->getMessage());
            return 0;
        }
    }
}
?>