<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AtividadeComplementarACC.php';
require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/LogAcoesController.php';
require_once __DIR__ . '/../services/HorasLimiteService.php';

use backend\api\config\Database;
use backend\api\models\AtividadeComplementarACC;
use backend\api\models\AtividadesDisponiveis;
use backend\api\controllers\Controller;
use backend\api\controllers\LogAcoesController;
use backend\api\services\HorasLimiteService;
use Exception;
use DateTime;

class AtividadeComplementarACCController extends Controller {
    
    /**
     * Cadastrar nova atividade complementar de extensão com JWT
     */
    public function cadastrarComJWT($aluno_id, $dados) {
        try {
            // VALIDAÇÃO CRÍTICA: Verificar se o aluno já atingiu o limite total de 240h
            $totalHorasAtual = HorasLimiteService::calcularTotalHorasAluno($aluno_id);
            if ($totalHorasAtual >= 240) {
                throw new Exception("🚫 Limite total de 240 horas já foi atingido. Não é possível cadastrar novas atividades em nenhuma categoria.");
            }
            
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
            
            // Validar se data_fim >= data_inicio usando DateTime para maior precisão
            try {
                $dataInicio = new DateTime($dados['data_inicio']);
                $dataFim = new DateTime($dados['data_fim']);
                
                // Permitir datas iguais para atividades de um dia
                if ($dataFim < $dataInicio) {
                    throw new Exception("A data de fim não pode ser anterior à data de início.");
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'data de fim') !== false) {
                    throw $e; // Re-lançar nossa exceção personalizada
                }
                throw new Exception("Formato de data inválido. Use o formato YYYY-MM-DD.");
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
            
            // CORREÇÃO CRÍTICA: Buscar matrícula do aluno PRIMEIRO para usar tabela correta
            $db = Database::getInstance()->getConnection();
            $stmtAluno = $db->prepare("SELECT a.matricula FROM aluno a WHERE a.usuario_id = ?");
            $stmtAluno->bind_param("i", $aluno_id);
            $stmtAluno->execute();
            $resultAluno = $stmtAluno->get_result();
            $alunoData = $resultAluno->fetch_assoc();
            
            if (!$alunoData) {
                throw new Exception("Dados do aluno não encontrados");
            }
            
            $matricula = $alunoData['matricula'];
            
            // Validar horas máximas da atividade usando a matrícula para buscar na tabela correta
            $atividade_disponivel = AtividadesDisponiveis::buscarPorId($dados['atividade_disponivel_id'], $matricula);
            if (!$atividade_disponivel) {
                throw new Exception("Atividade não encontrada");
            }
            
            if ($dados['horas_realizadas'] > $atividade_disponivel['carga_horaria_maxima_por_atividade']) {
                throw new Exception("Horas realizadas não podem exceder {$atividade_disponivel['carga_horaria_maxima_por_atividade']} horas");
            }
            
            // VALIDAÇÃO CRÍTICA: Verificar limite da categoria ACC (80h)
            // Calcular horas já cadastradas incluindo todas as atividades pendentes e aprovadas
            $horasJaCadastradas = $this->calcularHorasACCCompleta($aluno_id);
            $limiteACC = 80;
            $horasSolicitadas = $dados['horas_realizadas'];
            
            // Verificar se já atingiu o limite da categoria
            if ($horasJaCadastradas >= $limiteACC) {
                throw new Exception("🚫 Limite máximo de {$limiteACC} horas para atividades ACC já foi atingido. Você já possui {$horasJaCadastradas}h cadastradas nesta categoria (incluindo atividades pendentes de avaliação).");
            }
            
            // Verificar se a nova atividade excederia o limite da categoria
            $totalComNovaAtividade = $horasJaCadastradas + $horasSolicitadas;
            $ajusteAutomaticoACC = false;
            $mensagemAjusteACC = '';
            
            if ($totalComNovaAtividade > $limiteACC) {
                $horasRestantes = $limiteACC - $horasJaCadastradas;
                
                // Se não há horas restantes, bloquear o cadastro
                if ($horasRestantes <= 0) {
                    throw new Exception("🚫 Limite máximo de {$limiteACC} horas para atividades ACC já foi atingido. Você já possui {$horasJaCadastradas}h cadastradas nesta categoria (incluindo atividades pendentes de avaliação).");
                }
                
                // Ajustar automaticamente as horas para o limite restante
                $dados['horas_realizadas'] = $horasRestantes;
                $ajusteAutomaticoACC = true;
                $mensagemAjusteACC = "O total de horas contabilizadas será de {$horasRestantes} horas, pois você já possui {$horasJaCadastradas} horas cadastradas nesta categoria e o limite máximo é de {$limiteACC} horas.";
                
                // Log do ajuste automático
                error_log("AJUSTE AUTOMÁTICO ACC - Aluno ID: {$aluno_id}, Horas solicitadas: {$horasSolicitadas}, Horas ajustadas: {$horasRestantes}, Horas já cadastradas: {$horasJaCadastradas}");
            }
            
            // VALIDAÇÃO CRÍTICA: Verificar se é "Curso de extensão em áreas afins" e validar limite total
            $nomeAtividade = $atividade_disponivel['titulo'] ?? $atividade_disponivel['nome'] ?? '';
            error_log("DEBUG VALIDAÇÃO - Atividade ID: {$dados['atividade_disponivel_id']}, Nome: '{$nomeAtividade}'");
            
            // VALIDAÇÃO PARA "Curso de extensão na área específica" - LIMITE 40h ACUMULADAS
            if (strpos($nomeAtividade, 'Curso de extensão na área específica') !== false) {
                error_log("DEBUG: Validando limite para 'Curso de extensão na área específica'");
                error_log("DEBUG: Aluno ID: " . $aluno_id);
                error_log("DEBUG: Horas solicitadas: " . $dados['horas_realizadas']);
                
                // Limite fixo de 40 horas para esta atividade
                $limiteHoras = 40;
                
                // Buscar TODOS os IDs de atividades que contenham "Curso de extensão na área específica"
                $idsAtividades = [];
                
                // Buscar na tabela BCC23
                $sqlBCC23 = "SELECT id FROM atividadesdisponiveisbcc23 WHERE titulo LIKE '%Curso de extensão na área específica%'";
                $stmtBCC23 = $db->prepare($sqlBCC23);
                $stmtBCC23->execute();
                $resultBCC23 = $stmtBCC23->get_result();
                while ($row = $resultBCC23->fetch_assoc()) {
                    $idsAtividades[] = $row['id'];
                }
                
                // Buscar na tabela BCC17
                $sqlBCC17 = "SELECT id FROM atividadesdisponiveisbcc17 WHERE titulo LIKE '%Curso de extensão na área específica%'";
                $stmtBCC17 = $db->prepare($sqlBCC17);
                $stmtBCC17->execute();
                $resultBCC17 = $stmtBCC17->get_result();
                while ($row = $resultBCC17->fetch_assoc()) {
                    $idsAtividades[] = $row['id'];
                }
                
                error_log("DEBUG: IDs encontrados para 'Curso de extensão na área específica': " . implode(', ', $idsAtividades));
                
                // Buscar horas já cadastradas desta atividade específica em TODAS as tabelas
                $horasJaCadastradas = 0;
                
                // Verificar em todas as tabelas de atividades complementares
                $tabelas = [
                    'atividadecomplementaracc' => 'horas_realizadas',
                    'AtividadeComplementarEnsino' => 'carga_horaria',
                    'atividadecomplementarestagio' => 'horas',
                    'atividadecomplementarpesquisa' => 'horas_realizadas'
                ];
                
                foreach ($tabelas as $tabela => $campoHoras) {
                    if (!empty($idsAtividades)) {
                        $placeholders = str_repeat('?,', count($idsAtividades) - 1) . '?';
                        $sql = "SELECT SUM({$campoHoras}) as total_horas 
                                FROM {$tabela} 
                                WHERE aluno_id = ? 
                                AND atividade_disponivel_id IN ({$placeholders})
                                AND status IN ('Aguardando avaliação', 'aprovado')";
                                
                        $stmt = $db->prepare($sql);
                        $params = array_merge([$aluno_id], $idsAtividades);
                        $types = str_repeat('i', count($params));
                        $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        
                        $horasTabela = $row && $row['total_horas'] ? (int) $row['total_horas'] : 0;
                        error_log("DEBUG: Tabela {$tabela} - Horas encontradas: {$horasTabela}h");
                        
                        if ($horasTabela > 0) {
                            $horasJaCadastradas += $horasTabela;
                        }
                    }
                }
                
                error_log("DEBUG: Horas já cadastradas TOTAL: {$horasJaCadastradas}h");
                
                // Verificar se já atingiu o limite máximo
                if ($horasJaCadastradas >= $limiteHoras) {
                    throw new Exception("Limite máximo de {$limiteHoras}h atingido para 'Curso de extensão na área específica'. Você já possui {$horasJaCadastradas}h cadastradas.");
                }
                
                // Verificar se as novas horas excedem o limite
                $totalHoras = $horasJaCadastradas + $dados['horas_realizadas'];
                if ($totalHoras > $limiteHoras) {
                    $horasRestantes = $limiteHoras - $horasJaCadastradas;
                    throw new Exception("Você pode cadastrar no máximo {$horasRestantes}h adicionais para 'Curso de extensão na área específica' (limite total: {$limiteHoras}h, já cadastradas: {$horasJaCadastradas}h)");
                }
                
                error_log("DEBUG: Validação aprovada - Total após cadastro: {$totalHoras}h de {$limiteHoras}h");
            }
            // VALIDAÇÃO PARA "Curso de extensão em áreas afins"
            elseif (strpos($nomeAtividade, 'Curso de extensão em áreas afins') !== false) {
                // Matrícula já foi buscada anteriormente
                $anoMatricula = (int) substr($matricula, 0, 4);
                $limiteHoras = ($anoMatricula >= 2023) ? 10 : 20;
                
                // Determinar o ID correto da atividade baseado no ano da matrícula
                $atividadeIdCorreto = ($anoMatricula >= 2023) ? 29 : 28; // BCC23: ID 29, BCC17: ID 28
                
                // Buscar horas já cadastradas desta atividade específica em TODAS as tabelas
                $horasJaCadastradas = 0;
                $tabelas = [
                    'atividadecomplementaracc' => 'horas_realizadas',
                    'atividadecomplementarensino' => 'carga_horaria',
                    'atividadecomplementarestagio' => 'horas',
                    'atividadecomplementarpesquisa' => 'horas_realizadas'
                ];
                
                foreach ($tabelas as $tabela => $campoHoras) {
                    // Buscar por AMBOS os IDs (28 e 29) para garantir que não há duplicação entre BCC17 e BCC23
                    $sql = "SELECT SUM({$campoHoras}) as total_horas 
                            FROM {$tabela} 
                            WHERE aluno_id = ? 
                            AND atividade_disponivel_id IN (28, 29)
                            AND status IN ('Aguardando avaliação', 'aprovado')";
                            
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("i", $aluno_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    
                    $horasTabela = $row && $row['total_horas'] ? (int) $row['total_horas'] : 0;
                    error_log("DEBUG ÁREAS AFINS: Tabela {$tabela} - Horas encontradas: {$horasTabela}h");
                    
                    if ($horasTabela > 0) {
                        $horasJaCadastradas += $horasTabela;
                    }
                }
                
                // Log para debug
                error_log("VALIDAÇÃO CURSO EXTENSÃO - Aluno ID: {$aluno_id}, Matrícula: {$matricula}, Ano: {$anoMatricula}, Limite: {$limiteHoras}h, Já cadastradas: {$horasJaCadastradas}h, Nova atividade: {$dados['horas_realizadas']}h");
                
                // Verificar se já atingiu o limite máximo
                if ($horasJaCadastradas >= $limiteHoras) {
                    throw new Exception("⚠️ Limite máximo atingido para 'Curso de extensão em áreas afins'. Você já possui {$horasJaCadastradas}h cadastradas de um total permitido de {$limiteHoras}h. Não é possível cadastrar mais atividades desta categoria.");
                }
                
                // NOVA FUNCIONALIDADE: Ajuste automático de horas
                $totalComNovaAtividade = $horasJaCadastradas + $dados['horas_realizadas'];
                $mensagemAjuste = null;
                
                if ($totalComNovaAtividade > $limiteHoras) {
                    // Calcular horas restantes disponíveis
                    $horasRestantes = max(0, $limiteHoras - $horasJaCadastradas);
                    
                    if ($horasRestantes > 0) {
                        // Ajustar automaticamente as horas para o valor restante
                        $horasOriginais = $dados['horas_realizadas'];
                        $dados['horas_realizadas'] = $horasRestantes;
                        
                        // Criar mensagem informativa sobre o ajuste
                        $mensagemAjuste = "✅ Ajuste automático realizado: O total de horas contabilizadas será de {$horasRestantes} horas, pois você já possui {$horasJaCadastradas} horas cadastradas no sistema. (Horas solicitadas originalmente: {$horasOriginais}h)";
                        
                        error_log("AJUSTE AUTOMÁTICO - Horas originais: {$horasOriginais}h, Horas ajustadas: {$horasRestantes}h, Já cadastradas: {$horasJaCadastradas}h");
                    } else {
                        throw new Exception("⚠️ Limite máximo atingido para 'Curso de extensão em áreas afins'. Você já possui {$horasJaCadastradas}h cadastradas de um total permitido de {$limiteHoras}h. Não é possível cadastrar mais atividades desta categoria.");
                    }
                }
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
            
            
            
            // Adicionar aluno_id aos dados
            $dados['aluno_id'] = $aluno_id;
            
            $atividade_id = AtividadeComplementarACC::create($dados);

            if (!$atividade_id) {
                throw new Exception("Falha ao criar atividade");
            }
            
            // Buscar nome do usuário para o log
            $db = Database::getInstance()->getConnection();
            $stmtUsuario = $db->prepare("SELECT nome FROM Usuario WHERE id = ?");
            $stmtUsuario->bind_param("i", $aluno_id);
            $stmtUsuario->execute();
            $resultUsuario = $stmtUsuario->get_result();
            $usuarioData = $resultUsuario->fetch_assoc();
            $nomeUsuario = $usuarioData ? $usuarioData['nome'] : '';

            // Registrar log de ação
            LogAcoesController::registrar(
                $aluno_id,
                'CADASTRAR_ATIVIDADE_ACC',
                "Atividade de extensão '{$atividade_disponivel['titulo']}' cadastrada pelo usuário {$nomeUsuario}"
            );
            
            // Se houve ajuste automático, incluir a mensagem na resposta
            $response = [
                'success' => true,
                'message' => 'Atividade cadastrada com sucesso!',
                'atividade_id' => $atividade_id
            ];
            
            // Verificar ajuste automático para "Curso de extensão em áreas afins"
            if ($mensagemAjuste) {
                $response['ajuste_automatico'] = true;
                $response['mensagem_ajuste'] = $mensagemAjuste;
            }
            
            // Verificar ajuste automático para categoria ACC
            if ($ajusteAutomaticoACC) {
                $response['ajuste_automatico'] = true;
                $response['mensagem_ajuste'] = $mensagemAjusteACC;
            }
            
            return $response;
            
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
            
            if (empty($dados['status']) || !in_array($dados['status'], ['aprovado', 'rejeitado'])) {
                throw new Exception("Status deve ser 'aprovado' ou 'rejeitado'");
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
                $atividade['status'] === 'Aguardando avaliação') {
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
    
    /**
     * Calcular total de horas de ACC incluindo todas as atividades (pendentes e aprovadas)
     */
    private function calcularHorasACCCompleta($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Calcular horas de TODAS as atividades ACC (incluindo pendentes)
            $sql = "SELECT SUM(horas_realizadas) as total_horas 
                    FROM atividadecomplementaracc 
                    WHERE aluno_id = ? 
                    AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada', 'pendente')";
                    
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $horasCategoria = $row && $row['total_horas'] ? (int) $row['total_horas'] : 0;
            error_log("DEBUG CALC ACC COMPLETA - Horas calculadas para aluno {$aluno_id}: {$horasCategoria}h (incluindo pendentes)");
            
            return $horasCategoria;
            
        } catch (Exception $e) {
            error_log("Erro ao calcular horas completas da categoria ACC: " . $e->getMessage());
            return 0;
        }
    }
}
?>