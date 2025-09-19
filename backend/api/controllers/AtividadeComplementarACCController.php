<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AtividadeComplementarACC.php';
require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/LogAcoesController.php';

use backend\api\config\Database;
use backend\api\models\AtividadeComplementarACC;
use backend\api\models\AtividadesDisponiveis;
use backend\api\controllers\Controller;
use backend\api\controllers\LogAcoesController;
use Exception;

class AtividadeComplementarACCController extends Controller {
    
    /**
     * Cadastrar nova atividade complementar de extensão com JWT
     */
    public function cadastrarComJWT($dados) {
        try {
            // Validações específicas
            if (empty($dados['atividade_disponivel_id']) || !is_numeric($dados['atividade_disponivel_id'])) {
                throw new Exception("Atividade deve ser selecionada");
            }
            
            if (empty($dados['horas_realizadas']) || $dados['horas_realizadas'] <= 0) {
                throw new Exception("Horas realizadas deve ser maior que zero");
            }
            
            if (empty($dados['data_inicio']) || empty($dados['data_fim'])) {
                throw new Exception("Datas de início e fim são obrigatórias");
            }
            
            // Validar se data_fim >= data_inicio (permitir datas iguais para atividades de um dia)
            if (strtotime($dados['data_fim']) < strtotime($dados['data_inicio'])) {
                throw new Exception("A data de fim não pode ser anterior à data de início.");
            }
            
            // Remover validação que impede datas futuras - atividades podem ser planejadas para o futuro
            // $hoje = date('Y-m-d');
            // if ($dados['data_fim'] > $hoje) {
            //     throw new Exception("Data de fim não pode ser futura");
            // }
            
            if (empty($dados['local_instituicao']) || strlen(trim($dados['local_instituicao'])) < 3) {
                throw new Exception("Local/Instituição deve ter pelo menos 3 caracteres");
            }
            
            if (empty($dados['declaracao_caminho'])) {
                throw new Exception("Declaração/Certificado é obrigatório");
            }
            
            // Validar horas máximas da atividade
            $atividade_disponivel = AtividadesDisponiveis::buscarPorId($dados['atividade_disponivel_id']);
            if (!$atividade_disponivel) {
                throw new Exception("Atividade não encontrada");
            }
            
            if ($dados['horas_realizadas'] > $atividade_disponivel['carga_horaria_maxima_por_atividade']) {
                throw new Exception("Horas realizadas não podem exceder {$atividade_disponivel['carga_horaria_maxima_por_atividade']} horas");
            }
            
            // Garantir que curso_evento_nome seja sempre preenchido
            if (empty($dados['curso_evento_nome'])) {
                // Tentar obter de diferentes campos possíveis
                $curso_evento_nome = $dados['curso_nome'] ?? 
                                   $dados['evento_nome'] ?? 
                                   $dados['projeto_nome'] ?? 
                                   $_POST['curso_nome'] ?? 
                                   $_POST['evento_nome'] ?? 
                                   $_POST['projeto_nome'] ?? 
                                   null;
                
                if (empty($curso_evento_nome)) {
                    throw new Exception("Nome do curso/evento/projeto é obrigatório");
                }
                
                $dados['curso_evento_nome'] = $curso_evento_nome;
            }
            
            $atividade_id = AtividadeComplementarACC::create($dados);

            if (!$atividade_id) {
                throw new Exception("Falha ao criar atividade");
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
                'CADASTRAR_ATIVIDADE_ACC',
                "Atividade de extensão '{$atividade_disponivel['titulo']}' cadastrada pelo usuário {$nomeUsuario}"
            );

            return $atividade_id;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarACCController::cadastrarComJWT: " . $e->getMessage());
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

            $atividades = AtividadeComplementarACC::buscarPorAluno($aluno_id);
            
            return [
                'success' => true,
                'data' => $atividades
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarACCController::listarPorAluno: " . $e->getMessage());
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
            
            $atividade = AtividadeComplementarACC::buscarPorId($id);
            
            if (!$atividade) {
                throw new Exception("Atividade não encontrada");
            }
            
            return [
                'success' => true,
                'data' => $atividade
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarACCController::buscarPorId: " . $e->getMessage());
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
            $atividades = AtividadeComplementarACC::listarTodas($filtros);
            
            return [
                'success' => true,
                'data' => $atividades
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarACCController::listarTodas: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro interno do servidor'
            ];
        }
    }
    
    /**
     * Avaliar atividade (aprovar/rejeitar)
     */
    public function avaliarAtividade($dados) {
        try {
            if (empty($dados['id']) || !is_numeric($dados['id'])) {
                throw new Exception("ID da atividade é obrigatório");
            }
            
            if (empty($dados['status']) || !in_array($dados['status'], ['Aprovada', 'Rejeitada'])) {
                throw new Exception("Status deve ser 'Aprovada' ou 'Rejeitada'");
            }
            
            if (empty($dados['avaliador_id']) || !is_numeric($dados['avaliador_id'])) {
                throw new Exception("Avaliador é obrigatório");
            }
            
            $sucesso = AtividadeComplementarACC::atualizarStatus(
                $dados['id'],
                $dados['status'],
                $dados['avaliador_id'],
                $dados['observacoes_avaliacao'] ?? null
            );
            
            if (!$sucesso) {
                throw new Exception("Falha ao atualizar status da atividade");
            }
            
            // Buscar dados da atividade para o log
            $atividade = AtividadeComplementarACC::buscarPorId($dados['id']);
            
            // Registrar log de ação
            LogAcoesController::registrar(
                $dados['avaliador_id'],
                'AVALIAR_ATIVIDADE_ACC',
                "Atividade de extensão '{$atividade['atividade_nome']}' do aluno {$atividade['aluno_nome']} foi {$dados['status']}"
            );
            
            return [
                'success' => true,
                'message' => 'Atividade avaliada com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarACCController::avaliarAtividade: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Excluir atividade
     */
    public function excluir($id, $usuario_id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new Exception("ID inválido");
            }
            
            // Buscar atividade para verificar se pertence ao usuário
            $atividade = AtividadeComplementarACC::buscarPorId($id);
            if (!$atividade) {
                throw new Exception("Atividade não encontrada");
            }
            
            // Verificar se o usuário pode excluir (apenas o próprio aluno ou coordenador)
            session_start();
            $usuario_logado = $_SESSION['usuario'] ?? null;
            
            if (!$usuario_logado) {
                throw new Exception("Usuário não autenticado");
            }
            
            $pode_excluir = false;
            
            // Aluno pode excluir suas próprias atividades pendentes
            if ($usuario_logado['tipo'] === 'aluno' && 
                $atividade['aluno_id'] == $usuario_logado['id'] && 
                $atividade['status'] === 'Pendente') {
                $pode_excluir = true;
            }
            
            // Coordenador pode excluir qualquer atividade
            if ($usuario_logado['tipo'] === 'coordenador') {
                $pode_excluir = true;
            }
            
            if (!$pode_excluir) {
                throw new Exception("Você não tem permissão para excluir esta atividade");
            }
            
            $sucesso = AtividadeComplementarACC::excluir($id);
            
            if (!$sucesso) {
                throw new Exception("Falha ao excluir atividade");
            }
            
            // Registrar log de ação
            LogAcoesController::registrar(
                $usuario_id,
                'EXCLUIR_ATIVIDADE_ACC',
                "Atividade de extensão '{$atividade['atividade_nome']}' foi excluída"
            );
            
            return [
                'success' => true,
                'message' => 'Atividade excluída com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarACCController::excluir: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obter estatísticas das atividades de extensão
     */
    public function obterEstatisticas($aluno_id = null) {
        try {
            $filtros = [];
            if ($aluno_id) {
                $filtros['aluno_id'] = $aluno_id;
            }
            
            $atividades = AtividadeComplementarACC::listarTodas($filtros);
            
            $estatisticas = [
                'total' => count($atividades),
                'pendentes' => 0,
                'aprovadas' => 0,
                'rejeitadas' => 0,
                'horas_aprovadas' => 0
            ];
            
            foreach ($atividades as $atividade) {
                switch ($atividade['status']) {
                    case 'Pendente':
                        $estatisticas['pendentes']++;
                        break;
                    case 'Aprovada':
                        $estatisticas['aprovadas']++;
                        $estatisticas['horas_aprovadas'] += $atividade['horas_realizadas'];
                        break;
                    case 'Rejeitada':
                        $estatisticas['rejeitadas']++;
                        break;
                }
            }
            
            return [
                'success' => true,
                'data' => $estatisticas
            ];
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarACCController::obterEstatisticas: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro interno do servidor'
            ];
        }
    }
}
?>