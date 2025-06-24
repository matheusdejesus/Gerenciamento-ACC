<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AtividadeComplementar.php';
require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
require_once __DIR__ . '/Controller.php';

use backend\api\config\Database;
use backend\api\models\AtividadeComplementar;
use backend\api\models\AtividadesDisponiveis;
use backend\api\controllers\Controller;

class AtividadeComplementarController extends Controller {
    
    public function cadastrar() {
        try {
            // Verificar se o usuário está logado
            session_start();
            if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'aluno') {
                $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                return;
            }
            
            // Obter dados da requisição
            $dados = $_POST ?? [];
            $arquivos = $_FILES ?? [];
            
            // Log dos dados recebidos
            error_log("=== DADOS RECEBIDOS ===");
            error_log("POST: " . json_encode($dados));
            error_log("FILES: " . json_encode(array_keys($arquivos)));
            
            // Validar dados
            $erros = $this->validarDadosEntrada($dados);
            if (!empty($erros)) {
                $this->sendJsonResponse(['error' => implode(', ', $erros)], 400);
                return;
            }
            
            // Validar orientador
            if (empty($dados['orientador_id']) || !is_numeric($dados['orientador_id'])) {
                $this->sendJsonResponse(['error' => 'Orientador é obrigatório'], 400);
                return;
            }
            
            // Validar arquivo
            if (!isset($arquivos['declaracao']) || $arquivos['declaracao']['error'] !== UPLOAD_ERR_OK) {
                $this->sendJsonResponse(['error' => 'Declaração é obrigatória'], 400);
                return;
            }
            
            // Buscar informações da atividade disponível
            $atividadeDisponivel = AtividadesDisponiveis::buscarPorId($dados['atividade_id']);
            if (!$atividadeDisponivel) {
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            
            // Validar carga horária máxima
            if ($dados['horas_solicitadas'] > $atividadeDisponivel['horas_max']) {
                $this->sendJsonResponse(['error' => 'Carga horária solicitada excede o máximo permitido'], 400);
                return;
            }
            // Processar arquivo
            $declaracaoBlob = file_get_contents($arquivos['declaracao']['tmp_name']);
            
            // Preparar dados para inserção
            $dadosInsercao = [
                'aluno_id' => $_SESSION['usuario']['id'],
                'categoria_id' => $atividadeDisponivel['categoria_id'],
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao_atividades'],
                'data_inicio' => $dados['data_inicio'],
                'data_fim' => $dados['data_fim'],
                'carga_horaria_solicitada' => (int)$dados['horas_solicitadas'],
                'declaracao' => $declaracaoBlob,
                'orientador_id' => (int)$dados['orientador_id']
            ];
            
            error_log("=== DADOS PARA INSERÇÃO ===");
            error_log(json_encode($dadosInsercao));
            
            // Criar atividade complementar
            $atividadeId = AtividadeComplementar::create($dadosInsercao);
            
            if ($atividadeId) {
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Atividade cadastrada com sucesso',
                    'atividade_id' => $atividadeId
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Erro ao cadastrar atividade'], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::cadastrar: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
        }
    }
    
    public function listarPorAluno($aluno_id = null) {
        try {
            if ($aluno_id === null) {
                session_start();
                if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'aluno') {
                    $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                    return;
                }
                $aluno_id = $_SESSION['usuario']['id'];
            }
            
            $atividades = AtividadeComplementar::buscarPorAluno($aluno_id);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarPorAluno: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
    
    public function buscar($id) {
        try {
            session_start();
            if (empty($_SESSION['usuario'])) {
                $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                return;
            }
            
            $atividade = AtividadeComplementar::buscarPorId($id);
            
            if (!$atividade) {
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            // Verificar permissões
            if ($_SESSION['usuario']['tipo'] === 'aluno' && $atividade['aluno_id'] != $_SESSION['usuario']['id']) {
                $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                return;
            }
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividade
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::buscar: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
    
    public function buscarOrientadores() {
        try {
            $orientadores = AtividadeComplementar::buscarOrientadores();
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $orientadores
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::buscarOrientadores: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
    
    public function listarPendentesOrientador() {
        try {
            session_start();
            if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'orientador') {
                $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                return;
            }
            
            $atividades = AtividadeComplementar::buscarPendentesOrientador($_SESSION['usuario']['id']);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarPendentesOrientador: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
    
    public function avaliarAtividade() {
        try {
            session_start();
            if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'orientador') {
                $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                return;
            }
            
            // Obter dados da requisição
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->sendJsonResponse(['error' => 'Dados inválidos'], 400);
                return;
            }
            
            // Validar dados obrigatórios
            $erros = $this->validarDadosAvaliacao($input);
            if (!empty($erros)) {
                $this->sendJsonResponse(['error' => implode(', ', $erros)], 400);
                return;
            }
            
            $atividade_id = (int)$input['atividade_id'];
            $carga_horaria_aprovada = (int)$input['carga_horaria_aprovada'];
            $observacoes_analise = trim($input['observacoes_analise']);
            $status = $input['status']; // 'Aprovada' ou 'Rejeitada'
            $orientador_id = $_SESSION['usuario']['id'];
            
            // Validar status
            if (!in_array($status, ['Aprovada', 'Rejeitada'])) {
                $this->sendJsonResponse(['error' => 'Status inválido'], 400);
                return;
            }
            
            if ($status === 'Rejeitada') {
                $carga_horaria_aprovada = 0;
            }
            
            // Verificar se as horas aprovadas não excedem as solicitadas
            if ($status === 'Aprovada' && $carga_horaria_aprovada > 0) {
                $atividade = AtividadeComplementar::buscarPorId($atividade_id);
                if (!$atividade) {
                    $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                    return;
                }
                
                // Verificar se pertence ao orientador
                if ($atividade['orientador_id'] != $orientador_id) {
                    $this->sendJsonResponse(['error' => 'Acesso negado para esta atividade'], 403);
                    return;
                }
                
                // Verificar se não excede as horas solicitadas
                if ($carga_horaria_aprovada > $atividade['carga_horaria_solicitada']) {
                    $this->sendJsonResponse([
                        'error' => "Não é possível aprovar mais horas ({$carga_horaria_aprovada}h) do que o aluno solicitou ({$atividade['carga_horaria_solicitada']}h)"
                    ], 400);
                    return;
                }
            }
            
            // Avaliar a atividade
            $sucesso = AtividadeComplementar::avaliarAtividade(
                $atividade_id,
                $orientador_id,
                $carga_horaria_aprovada,
                $observacoes_analise,
                $status
            );
            
            if ($sucesso) {
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Atividade avaliada com sucesso',
                    'status' => $status,
                    'horas_aprovadas' => $carga_horaria_aprovada
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Erro ao avaliar atividade'], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::avaliarAtividade: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
        }
    }
    
    public function listarAvaliadasOrientador() {
        try {
            session_start();
            if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'orientador') {
                $this->sendJsonResponse(['error' => 'Acesso negado'], 403);
                return;
            }
            
            $atividades = AtividadeComplementar::buscarAvaliadasOrientador($_SESSION['usuario']['id']);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarAvaliadasOrientador: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
    
    public function downloadDeclaracao($atividade_id) {
        try {
            session_start();
            if (empty($_SESSION['usuario'])) {
                http_response_code(403);
                echo 'Acesso negado';
                return;
            }
            
            $atividade = AtividadeComplementar::buscarPorId($atividade_id);
            
            if (!$atividade) {
                http_response_code(404);
                echo 'Atividade não encontrada';
                return;
            }
            
            // Verificar permissões
            $usuario_tipo = $_SESSION['usuario']['tipo'];
            $usuario_id = $_SESSION['usuario']['id'];
            
            $tem_permissao = false;
            
            if ($usuario_tipo === 'aluno' && $atividade['aluno_id'] == $usuario_id) {
                $tem_permissao = true;
            } elseif ($usuario_tipo === 'orientador' && $atividade['orientador_id'] == $usuario_id) {
                $tem_permissao = true;
            } elseif ($usuario_tipo === 'coordenador') {
                $tem_permissao = true;
            }
            
            if (!$tem_permissao) {
                http_response_code(403);
                echo 'Acesso negado para este documento';
                return;
            }
            
            // Buscar a declaração
            $declaracao = $this->buscarDeclaracaoBlob($atividade_id);
            
            if (!$declaracao) {
                http_response_code(404);
                echo 'Documento não encontrado';
                return;
            }
            
            // Limpar headers JSON anteriores e definir headers para PDF
            header_remove('Content-Type');
            header_remove('Access-Control-Allow-Origin');
            header_remove('Access-Control-Allow-Methods');
            header_remove('Access-Control-Allow-Headers');
            
            // Definir headers para download de PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="declaracao_atividade_' . $atividade_id . '.pdf"');
            header('Content-Length: ' . strlen($declaracao));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $declaracao;
            exit;
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::downloadDeclaracao: " . $e->getMessage());
            http_response_code(500);
            echo 'Erro interno do servidor';
        }
    }

    private function buscarDeclaracaoBlob($atividade_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT declaracao FROM AtividadeComplementar WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $atividade_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row ? $row['declaracao'] : null;
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar declaração: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function validarDadosEntrada($dados) {
        $erros = [];
        
        if (empty($dados['atividade_id']) || !is_numeric($dados['atividade_id'])) {
            $erros[] = "ID da atividade é obrigatório";
        }
        
        if (empty($dados['titulo'])) {
            $erros[] = "Título é obrigatório";
        }
        
        if (empty($dados['data_inicio'])) {
            $erros[] = "Data de início é obrigatória";
        }
        
        if (empty($dados['data_fim'])) {
            $erros[] = "Data de término é obrigatória";
        }
        
        if (empty($dados['horas_solicitadas']) || !is_numeric($dados['horas_solicitadas']) || $dados['horas_solicitadas'] <= 0) {
            $erros[] = "Carga horária deve ser um número maior que zero";
        }
        
        if (empty($dados['orientador_id']) || !is_numeric($dados['orientador_id'])) {
            $erros[] = "Orientador é obrigatório";
        }
        
        if (empty($dados['descricao_atividades'])) {
            $erros[] = "Descrição das atividades é obrigatória";
        }
        
        // Validar datas
        if (!empty($dados['data_inicio']) && !empty($dados['data_fim'])) {
            $dataInicio = new \DateTime($dados['data_inicio']);
            $dataFim = new \DateTime($dados['data_fim']);
            
            if ($dataInicio > $dataFim) {
                $erros[] = "Data de início deve ser anterior à data de término";
            }
        }
        
        return $erros;
    }
    
    private function processarArquivo($arquivo) {
        // Validar tipo de arquivo
        $tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            throw new \Exception('Tipo de arquivo não permitido. Use PDF, JPG, JPEG ou PNG.');
        }
        
        // Validar tamanho (5MB máximo)
        $tamanhoMaximo = 5 * 1024 * 1024;
        if ($arquivo['size'] > $tamanhoMaximo) {
            throw new \Exception('Arquivo muito grande. Tamanho máximo: 5MB.');
        }
        
        // Ler conteúdo do arquivo
        $conteudo = file_get_contents($arquivo['tmp_name']);
        if ($conteudo === false) {
            throw new \Exception('Erro ao ler arquivo.');
        }
        
        return $conteudo;
    }
    
    private function obterCategoriaId($atividade) {
        return $atividade['categoria_id'] ?? 1;
    }
    
    private function validarDadosAvaliacao($dados) {
        $erros = [];
        
        if (empty($dados['atividade_id']) || !is_numeric($dados['atividade_id'])) {
            $erros[] = "ID da atividade é obrigatório";
        }
        
        if (!isset($dados['carga_horaria_aprovada']) || !is_numeric($dados['carga_horaria_aprovada']) || $dados['carga_horaria_aprovada'] < 0) {
            $erros[] = "Carga horária aprovada deve ser um número maior ou igual a zero";
        }
        
        if (empty($dados['observacoes_analise'])) {
            $erros[] = "Observações/parecer são obrigatórias";
        }
        
        if (empty($dados['status'])) {
            $erros[] = "Status da avaliação é obrigatório";
        }
        
        return $erros;
    }

    public function cadastrarComJWT($aluno_id) {
        try {
            // Obter dados da requisição
            $dados = $_POST ?? [];
            $arquivos = $_FILES ?? [];
            
            // Log dos dados recebidos
            error_log("=== DADOS RECEBIDOS (JWT) ===");
            error_log("POST: " . json_encode($dados));
            error_log("FILES: " . json_encode(array_keys($arquivos)));
            error_log("Aluno ID: " . $aluno_id);
            
            // Validar dados
            $erros = $this->validarDadosEntrada($dados);
            if (!empty($erros)) {
                $this->sendJsonResponse(['error' => implode(', ', $erros)], 400);
                return;
            }
            
            // Validar orientador
            if (empty($dados['orientador_id']) || !is_numeric($dados['orientador_id'])) {
                $this->sendJsonResponse(['error' => 'Orientador é obrigatório'], 400);
                return;
            }
            
            // Validar arquivo
            if (!isset($arquivos['declaracao']) || $arquivos['declaracao']['error'] !== UPLOAD_ERR_OK) {
                $this->sendJsonResponse(['error' => 'Declaração é obrigatória'], 400);
                return;
            }
            
            // Buscar informações da atividade disponível
            $atividadeDisponivel = AtividadesDisponiveis::buscarPorId($dados['atividade_id']);
            if (!$atividadeDisponivel) {
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            
            // Validar carga horária máxima
            if ($dados['horas_solicitadas'] > $atividadeDisponivel['horas_max']) {
                $this->sendJsonResponse(['error' => 'Carga horária solicitada excede o máximo permitido'], 400);
                return;
            }
            
            // Processar arquivo
            $declaracaoBlob = file_get_contents($arquivos['declaracao']['tmp_name']);
            
            // Preparar dados para inserção
            $dadosInsercao = [
                'aluno_id' => $aluno_id,
                'categoria_id' => $atividadeDisponivel['categoria_id'],
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao_atividades'],
                'data_inicio' => $dados['data_inicio'],
                'data_fim' => $dados['data_fim'],
                'carga_horaria_solicitada' => (int)$dados['horas_solicitadas'],
                'declaracao' => $declaracaoBlob,
                'orientador_id' => (int)$dados['orientador_id']
            ];
            
            error_log("=== DADOS PARA INSERÇÃO (JWT) ===");
            error_log(json_encode($dadosInsercao));
            
            // Criar atividade complementar
            $atividadeId = AtividadeComplementar::create($dadosInsercao);
            
            if ($atividadeId) {
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Atividade cadastrada com sucesso',
                    'atividade_id' => $atividadeId
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Erro ao cadastrar atividade'], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::cadastrarComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
        }
    }

    public function listarOrientadores() {
        try {
            $orientadores = AtividadeComplementar::listarOrientadores();
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $orientadores,
                'total' => count($orientadores)
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarOrientadores: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false, 
                'error' => 'Erro ao buscar orientadores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarPendentesOrientadorComJWT($orientador_id) {
        try {
            $atividades = AtividadeComplementar::buscarPendentesOrientador($orientador_id);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarPendentesOrientadorComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function listarAvaliadasOrientadorComJWT($orientador_id) {
        try {
            $atividades = AtividadeComplementar::buscarAvaliadasOrientador($orientador_id);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $atividades
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarAvaliadasOrientadorComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function avaliarAtividadeComJWT($orientador_id) {
        try {
            // Obter dados da requisição
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->sendJsonResponse(['error' => 'Dados inválidos'], 400);
                return;
            }
            
            // Validar dados obrigatórios
            $erros = $this->validarDadosAvaliacao($input);
            if (!empty($erros)) {
                $this->sendJsonResponse(['error' => implode(', ', $erros)], 400);
                return;
            }
            
            $atividade_id = (int)$input['atividade_id'];
            $carga_horaria_aprovada = (int)$input['carga_horaria_aprovada'];
            $observacoes_analise = trim($input['observacoes_analise']);
            $status = $input['status'];
            
            // Validar status
            if (!in_array($status, ['Aprovada', 'Rejeitada'])) {
                $this->sendJsonResponse(['error' => 'Status inválido'], 400);
                return;
            }
            
            if ($status === 'Rejeitada') {
                $carga_horaria_aprovada = 0;
            }
            
            // Verificar se as horas aprovadas não excedem as solicitadas
            if ($status === 'Aprovada' && $carga_horaria_aprovada > 0) {
                $atividade = AtividadeComplementar::buscarPorId($atividade_id);
                if (!$atividade) {
                    $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                    return;
                }
                
                // Verificar se pertence ao orientador
                if ($atividade['orientador_id'] != $orientador_id) {
                    $this->sendJsonResponse(['error' => 'Acesso negado para esta atividade'], 403);
                    return;
                }
                
                // Verificar se não excede as horas solicitadas
                if ($carga_horaria_aprovada > $atividade['carga_horaria_solicitada']) {
                    $this->sendJsonResponse([
                        'error' => "Não é possível aprovar mais horas ({$carga_horaria_aprovada}h) do que o aluno solicitou ({$atividade['carga_horaria_solicitada']}h)"
                    ], 400);
                    return;
                }
            }
            
            // Avaliar a atividade
            $sucesso = AtividadeComplementar::avaliarAtividade(
                $atividade_id,
                $orientador_id,
                $carga_horaria_aprovada,
                $observacoes_analise,
                $status
            );
            
            if ($sucesso) {
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Atividade avaliada com sucesso',
                    'status' => $status,
                    'horas_aprovadas' => $carga_horaria_aprovada
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Erro ao avaliar atividade'], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::avaliarAtividadeComJWT: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
        }
    }
}
?>