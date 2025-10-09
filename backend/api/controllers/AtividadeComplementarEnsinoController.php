<?php
require_once __DIR__ . '/../models/AtividadeComplementarEnsino.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/HorasLimiteService.php';

use backend\api\models\AtividadeComplementarEnsino;
use backend\api\middleware\AuthMiddleware;
use backend\api\config\Database;
use backend\api\services\HorasLimiteService;
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

            // VALIDAÇÃO CRÍTICA: Verificar se o aluno já atingiu o limite total de 240h
            $totalHorasAtual = HorasLimiteService::calcularTotalHorasAluno($usuario['id']);
            
            if ($totalHorasAtual >= 240) {
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
            if (empty($input['tipo_atividade'])) {
                error_log("Erro: Tipo de atividade não informado");
                http_response_code(400);
                echo json_encode(['erro' => 'Tipo de atividade é obrigatório']);
                return;
            }
            
            error_log("Tipo de atividade recebido: " . $input['tipo_atividade']);

            // Adicionar o ID do aluno logado
            $input['aluno_id'] = $usuario['id'];
            
            // VALIDAÇÃO CRÍTICA: Verificar limite da categoria Ensino (80h)
            $horasAtualEnsino = HorasLimiteService::calcularHorasCategoria($usuario['id'], 'ensino');
            $limiteEnsino = HorasLimiteService::getLimiteCategoria('ensino');
            $horasSolicitadas = $input['carga_horaria'] ?? 0;
            
            // Verificar se já atingiu o limite da categoria
            if ($horasAtualEnsino >= $limiteEnsino) {
                http_response_code(400);
                echo json_encode(['erro' => "🚫 Limite máximo de {$limiteEnsino} horas para atividades de Ensino já foi atingido. Você já possui {$horasAtualEnsino}h cadastradas nesta categoria."]);
                return;
            }
            
            // Verificar se a nova atividade excederia o limite da categoria
            $totalComNovaAtividade = $horasAtualEnsino + $horasSolicitadas;
            if ($totalComNovaAtividade > $limiteEnsino) {
                $horasRestantes = $limiteEnsino - $horasAtualEnsino;
                http_response_code(400);
                echo json_encode(['erro' => "⚠️ Limite da categoria Ensino seria excedido. Você possui {$horasAtualEnsino}h cadastradas e pode adicionar no máximo {$horasRestantes}h adicionais nesta categoria. Reduza as horas desta atividade para prosseguir."]);
                return;
            }

            // NOVA FUNCIONALIDADE: Validação de limite de 80h para alunos 2017-2022
            $this->validarLimite80hCategoriaEnsino($usuario['id'], $input['carga_horaria'] ?? 0);

            // Validar campos específicos baseado na categoria
            $tipo_atividade = $input['tipo_atividade'] ?? '';
            $input['categoria_id'] = 1; // Garantir categoria de Ensino
            
            // Garantir que atividade_disponivel_id está presente (obrigatório no banco)
            if (empty($input['atividade_disponivel_id'])) {
                // Buscar matrícula do aluno para determinar a tabela
                try {
                    $db = Database::getInstance()->getConnection();
                    $stmt_aluno = $db->prepare("SELECT matricula FROM Aluno WHERE usuario_id = ?");
                    $stmt_aluno->bind_param("i", $usuario['id']);
                    $stmt_aluno->execute();
                    $result_aluno = $stmt_aluno->get_result();
                    $aluno_data = $result_aluno->fetch_assoc();
                    $matricula = $aluno_data ? $aluno_data['matricula'] : null;
                    
                    // Determinar tabela baseada na matrícula
                    $ano_matricula = substr($matricula, 0, 4);
                    $tabela_atividades = ($ano_matricula >= '2023') ? 'atividadesdisponiveisbcc23' : 'atividadesdisponiveisbcc17';
                    
                    $sql = "SELECT id FROM {$tabela_atividades} WHERE categoria_id = 1 LIMIT 1";
                    $stmt = $db->prepare($sql);
                    
                    if ($stmt && $stmt->execute()) {
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $input['atividade_disponivel_id'] = $row['id'];
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Erro ao buscar atividade disponível: " . $e->getMessage());
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
    
    /**
     * NOVA FUNCIONALIDADE: Validar limite específico de 80h para categoria Ensino (alunos 2017-2022)
     */
    private function validarLimite80hCategoriaEnsino($aluno_id, $horas_solicitadas) {
        try {
            // Buscar matrícula do aluno
            $db = Database::getInstance()->getConnection();
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
                error_log("DEBUG LIMITE ENSINO 80H - Aluno elegível detectado. Matrícula: {$matricula}, Ano: {$anoMatricula}");
                
                // Calcular total de horas já utilizadas APENAS na categoria Ensino
                $horasEnsinoAtual = $this->calcularHorasCategoriaEnsino($aluno_id);
                error_log("DEBUG LIMITE ENSINO 80H - Horas Ensino atuais: {$horasEnsinoAtual}h");
                
                // Verificar se já atingiu o limite de 80h para Ensino
                if ($horasEnsinoAtual >= 80) {
                    throw new Exception("🚫 Limite máximo de 80 horas atingido para atividades de Ensino. Você já possui {$horasEnsinoAtual}h cadastradas nesta categoria. Não é possível cadastrar novas atividades de Ensino.");
                }
                
                // Verificar se a nova atividade excederia o limite de 80h para Ensino
                $totalComNovaAtividade = $horasEnsinoAtual + $horas_solicitadas;
                if ($totalComNovaAtividade > 80) {
                    $horasRestantes = 80 - $horasEnsinoAtual;
                    throw new Exception("⚠️ Limite de 80 horas para categoria Ensino seria excedido. Você possui {$horasEnsinoAtual}h cadastradas em Ensino e pode adicionar no máximo {$horasRestantes}h adicionais nesta categoria. Reduza as horas desta atividade para prosseguir.");
                }
                
                error_log("DEBUG LIMITE ENSINO 80H - Validação aprovada. Total Ensino após cadastro: {$totalComNovaAtividade}h de 80h");
            }
            
        } catch (Exception $e) {
            error_log("Erro na validação de limite categoria Ensino: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * NOVA FUNCIONALIDADE: Calcular horas específicas da categoria Ensino para limite de 80h
     * Soma apenas horas da tabela AtividadeComplementarEnsino para alunos 2017-2022
     */
    private function calcularHorasCategoriaEnsino($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT SUM(carga_horaria) as total_horas 
                    FROM AtividadeComplementarEnsino 
                    WHERE aluno_id = ? 
                    AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
                    
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $horasEnsino = $row && $row['total_horas'] ? (int) $row['total_horas'] : 0;
            error_log("DEBUG CALC ENSINO - Total Ensino para aluno {$aluno_id}: {$horasEnsino}h");
            
            return $horasEnsino;
            
        } catch (Exception $e) {
            error_log("Erro ao calcular horas categoria Ensino: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * NOVA FUNCIONALIDADE: Calcular total de horas extracurriculares para limite de 80h
     */
    private function calcularHorasExtracurricularesTotais($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
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
                error_log("DEBUG CALC 80H ENSINO - Tabela {$tabela}: {$horasTabela}h");
                
                $horasTotal += $horasTabela;
            }
            
            error_log("DEBUG CALC 80H ENSINO - Total calculado para aluno {$aluno_id}: {$horasTotal}h");
            return $horasTotal;
            
        } catch (Exception $e) {
            error_log("Erro ao calcular horas extracurriculares totais: " . $e->getMessage());
            return 0;
        }
    }
}