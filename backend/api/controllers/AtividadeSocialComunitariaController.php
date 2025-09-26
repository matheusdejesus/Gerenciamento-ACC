<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AtividadeSocialComunitaria.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/LogAcoesController.php';

use backend\api\config\Database;
use backend\api\models\AtividadeSocialComunitaria;
use backend\api\controllers\Controller;
use backend\api\controllers\LogAcoesController;
use Exception;
use DateTime;

class AtividadeSocialComunitariaController extends Controller {
    
    /**
     * Cadastrar nova atividade social comunitária com JWT
     */
    public function cadastrarComJWT($dados) {
        try {
            // Validações específicas
            if (empty($dados['nome_projeto']) || strlen(trim($dados['nome_projeto'])) < 3) {
                throw new Exception("Nome do projeto deve ter pelo menos 3 caracteres");
            }
            
            if (empty($dados['instituicao']) || strlen(trim($dados['instituicao'])) < 3) {
                throw new Exception("Instituição deve ter pelo menos 3 caracteres");
            }
            
            if (empty($dados['carga_horaria']) || $dados['carga_horaria'] <= 0) {
                throw new Exception("Carga horária deve ser maior que zero");
            }
            
            if ($dados['carga_horaria'] > 30) {
                throw new Exception("Carga horária não pode exceder 30 horas");
            }
            
            if (empty($dados['descricao_atividades']) || strlen(trim($dados['descricao_atividades'])) < 10) {
                throw new Exception("Descrição das atividades deve ter pelo menos 10 caracteres");
            }
            
            if (empty($dados['local_realizacao'])) {
                $dados['local_realizacao'] = $dados['instituicao'];
            }
            
            // Validar se já existe uma atividade social para este aluno
            $atividadesExistentes = AtividadeSocialComunitaria::buscarPorAluno($dados['aluno_id']);
            $horasJaCadastradas = 0;
            
            foreach ($atividadesExistentes as $atividade) {
                if ($atividade['status'] === 'aprovada') {
                    $horasJaCadastradas += $atividade['carga_horaria'];
                }
            }
            
            if (($horasJaCadastradas + $dados['carga_horaria']) > 30) {
                throw new Exception("Total de horas em atividades sociais não pode exceder 30 horas. Você já possui {$horasJaCadastradas} horas aprovadas.");
            }
            
            $atividade_id = AtividadeSocialComunitaria::create($dados);

            if (!$atividade_id) {
                throw new Exception("Falha ao criar atividade social comunitária");
            }
            
            // Buscar nome do usuário para o log
            $db = Database::getInstance()->getConnection();
            $stmtUsuario = $db->prepare("SELECT nome FROM Usuario WHERE id = ?");
            $stmtUsuario->bind_param("i", $dados['aluno_id']);
            $stmtUsuario->execute();
            $resultUsuario = $stmtUsuario->get_result();
            $usuarioData = $resultUsuario->fetch_assoc();
            $nomeUsuario = $usuarioData ? $usuarioData['nome'] : '';

            // Registrar log de ação
            LogAcoesController::registrar(
                $dados['aluno_id'],
                'CADASTRAR_ATIVIDADE_SOCIAL',
                "Atividade social comunitária '{$dados['nome_projeto']}' cadastrada pelo usuário {$nomeUsuario}"
            );

            return $atividade_id;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitariaController::cadastrarComJWT: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Listar atividades por aluno
     */
    public function listarPorAluno($aluno_id = null) {
        try {
            if ($aluno_id === null) {
                session_start();
                if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'aluno') {
                    return [
                        'success' => false,
                        'error' => 'Acesso negado'
                    ];
                }
                $aluno_id = $_SESSION['usuario']['id'];
            }

            $atividades = AtividadeSocialComunitaria::buscarPorAluno($aluno_id);
            
            return [
                'success' => true,
                'data' => $atividades
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitariaController::listarPorAluno: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro interno do servidor'
            ];
        }
    }
    
    /**
     * Buscar atividade por ID
     */
    public function buscarPorId($id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new Exception("ID inválido");
            }
            
            $atividade = AtividadeSocialComunitaria::buscarPorId($id);
            
            if (!$atividade) {
                throw new Exception("Atividade não encontrada");
            }
            
            return [
                'success' => true,
                'data' => $atividade
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitariaController::buscarPorId: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Listar todas as atividades (para coordenadores)
     */
    public function listarTodas($filtros = []) {
        try {
            $atividades = AtividadeSocialComunitaria::listarTodas($filtros);
            
            return [
                'success' => true,
                'data' => $atividades
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitariaController::listarTodas: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro interno do servidor'
            ];
        }
    }
    
    /**
     * Atualizar status da atividade (para coordenadores)
     */
    public function atualizarStatus($dados) {
        try {
            if (empty($dados['id']) || !is_numeric($dados['id'])) {
                throw new Exception("ID da atividade é obrigatório");
            }
            
            if (empty($dados['status']) || !in_array($dados['status'], ['pendente', 'aprovada', 'rejeitada'])) {
                throw new Exception("Status inválido");
            }
            
            $observacoes_avaliacao = $dados['observacoes_avaliacao'] ?? null;
            $avaliador_id = $dados['avaliador_id'] ?? null;
            
            $sucesso = AtividadeSocialComunitaria::atualizarStatus(
                $dados['id'], 
                $dados['status'], 
                $observacoes_avaliacao, 
                $avaliador_id
            );
            
            if (!$sucesso) {
                throw new Exception("Falha ao atualizar status da atividade");
            }
            
            // Buscar dados da atividade para o log
            $atividade = AtividadeSocialComunitaria::buscarPorId($dados['id']);
            
            // Registrar log de ação
            LogAcoesController::registrar(
                $avaliador_id ?? 0,
                'AVALIAR_ATIVIDADE_SOCIAL',
                "Atividade social '{$atividade['nome_projeto']}' do aluno {$atividade['aluno_nome']} foi {$dados['status']}"
            );
            
            return [
                'success' => true,
                'message' => 'Status atualizado com sucesso'
            ];
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitariaController::atualizarStatus: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Buscar atividades disponíveis da categoria 5 (ação social)
     */
    public function buscarAtividadesDisponiveis() {
        try {
            error_log("[DEBUG] Controller - Chamando método buscarAtividadesDisponiveis do model");
            
            // Chama o método do model para buscar as atividades
            $atividades = AtividadeSocialComunitaria::buscarAtividadesDisponiveis();
            
            error_log("[DEBUG] Controller - Atividades retornadas do model: " . count($atividades));
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitariaController::buscarAtividadesDisponiveis: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Excluir atividade (apenas se status for pendente)
     */
    public function excluir($id, $aluno_id = null) {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new Exception("ID inválido");
            }
            
            $atividade = AtividadeSocialComunitaria::buscarPorId($id);
            
            if (!$atividade) {
                throw new Exception("Atividade não encontrada");
            }
            
            // Verificar se o aluno pode excluir (apenas suas próprias atividades pendentes)
            if ($aluno_id && $atividade['aluno_id'] != $aluno_id) {
                throw new Exception("Você não tem permissão para excluir esta atividade");
            }
            
            if ($atividade['status'] !== 'pendente') {
                throw new Exception("Apenas atividades pendentes podem ser excluídas");
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM atividadessociaiscomunitarias WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Falha ao excluir atividade");
            }
            
            // Registrar log de ação
            LogAcoesController::registrar(
                $aluno_id ?? 0,
                'EXCLUIR_ATIVIDADE_SOCIAL',
                "Atividade social '{$atividade['nome_projeto']}' foi excluída"
            );
            
            return [
                'success' => true,
                'message' => 'Atividade excluída com sucesso'
            ];
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitariaController::excluir: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>