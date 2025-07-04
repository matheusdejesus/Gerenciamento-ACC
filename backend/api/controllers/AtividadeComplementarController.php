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
    
    //Cadastrar Atividade
    public function cadastrarComJWT($dados) {
        try {
            // Log dos dados recebidos
            error_log("Dados recebidos para cadastro: " . json_encode($dados));
            
            // Validações específicas
            if (empty($dados['titulo']) || strlen(trim($dados['titulo'])) < 3) {
                throw new Exception("Título deve ter pelo menos 3 caracteres");
            }
            
            if (empty($dados['data_inicio']) || empty($dados['data_fim'])) {
                throw new Exception("Datas de início e fim são obrigatórias");
            }
            
            // Validar se data_fim > data_inicio
            if (strtotime($dados['data_fim']) <= strtotime($dados['data_inicio'])) {
                throw new Exception("Data de término deve ser posterior à data de início");
            }
            
            if (empty($dados['carga_horaria_solicitada']) || $dados['carga_horaria_solicitada'] <= 0) {
                throw new Exception("Carga horária solicitada deve ser maior que zero");
            }
            
            if (empty($dados['orientador_id']) || !is_numeric($dados['orientador_id'])) {
                throw new Exception("Orientador deve ser selecionado");
            }
            
            $atividade_id = AtividadeComplementar::create($dados);

            if (!$atividade_id) {
                throw new Exception("Falha ao criar atividade");
            }

            return $atividade_id;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarController::cadastrarComJWT: " . $e->getMessage());
            throw $e;
        }
    }

    // Listar Orientadores
    public function listarOrientadores() {

        try {
            
            $orientadores = AtividadeComplementar::listarOrientadores();
            
            echo json_encode([
                'success' => true,
                'data' => $orientadores
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadeComplementarController::listarOrientadores: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false, 
                'error' => 'Erro ao buscar orientadores: ' . $e->getMessage()
            ], 500);
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
            
            error_log("Dados recebidos para avaliação: " . json_encode($input));
            error_log("Orientador ID: " . $orientador_id);
            
            if (!$input) {
                $this->sendJsonResponse(['error' => 'Dados inválidos ou ausentes'], 400);
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
                $this->sendJsonResponse(['error' => 'Status inválido. Deve ser "Aprovada" ou "Rejeitada"'], 400);
                return;
            }
            
            // Para atividades rejeitadas, carga horária deve ser 0
            if ($status === 'Rejeitada') {
                $carga_horaria_aprovada = 0;
            }
            
            // Validar carga horária para atividades aprovadas
            if ($status === 'Aprovada' && $carga_horaria_aprovada <= 0) {
                $this->sendJsonResponse(['error' => 'Para aprovar, a carga horária deve ser maior que zero'], 400);
                return;
            }
            
            // Verificar se a atividade existe e pertence ao orientador
            $atividade = AtividadeComplementar::buscarPorId($atividade_id);
            if (!$atividade) {
                $this->sendJsonResponse(['error' => 'Atividade não encontrada'], 404);
                return;
            }
            
            // Verificar se pertence ao orientador
            if ($atividade['orientador_id'] != $orientador_id) {
                $this->sendJsonResponse(['error' => 'Você não tem permissão para avaliar esta atividade'], 403);
                return;
            }
            
            // Verificar se já foi avaliada
            if ($atividade['status'] !== 'Pendente') {
                $this->sendJsonResponse(['error' => 'Esta atividade já foi avaliada'], 400);
                return;
            }
            
            // Verificar se não excede as horas solicitadas
            if ($status === 'Aprovada' && $carga_horaria_aprovada > $atividade['carga_horaria_solicitada']) {
                $this->sendJsonResponse([
                    'error' => "Não é possível aprovar mais horas ({$carga_horaria_aprovada}h) do que o aluno solicitou ({$atividade['carga_horaria_solicitada']}h)"
                ], 400);
                return;
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
                    'message' => "Atividade {$status} com sucesso",
                    'status' => $status,
                    'horas_aprovadas' => $carga_horaria_aprovada,
                    'atividade_id' => $atividade_id
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Falha ao salvar avaliação no banco de dados'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarController::avaliarAtividadeComJWT: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendJsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    private function validarDadosAvaliacao($dados) {
        $erros = [];
        
        if (empty($dados['atividade_id']) || !is_numeric($dados['atividade_id'])) {
            $erros[] = 'ID da atividade é obrigatório';
        }
        
        if (empty($dados['status'])) {
            $erros[] = 'Status é obrigatório';
        }
        
        if (!isset($dados['carga_horaria_aprovada']) || !is_numeric($dados['carga_horaria_aprovada'])) {
            $erros[] = 'Carga horária aprovada deve ser um número';
        }
        
        if (empty($dados['observacoes_analise'])) {
            $erros[] = 'Parecer/observações são obrigatórios';
        }
        
        return $erros;
    }
}

if (isset($_GET['orientadores'])) {
    $controller = new AtividadeComplementarController();
    $controller->listarOrientadores();
    exit;
}
?>