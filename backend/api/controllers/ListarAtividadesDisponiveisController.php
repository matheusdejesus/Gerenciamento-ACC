<?php

namespace backend\api\controllers;

require_once __DIR__ . '/../models/ListarAtividadesDisponiveisModel.php';

use backend\api\models\ListarAtividadesDisponiveisModel;
use Exception;

class ListarAtividadesDisponiveisController {
    
    /**
     * Retorna uma resposta de sucesso padronizada
     * @param mixed $data Dados a serem retornados
     * @param string $message Mensagem de sucesso
     * @return array Resposta formatada
     */
    private static function success($data = null, $message = 'Operação realizada com sucesso') {
        return [
            'success' => true,
            'sucesso' => true,
            'message' => $message,
            'data' => $data
        ];
    }
    
    /**
     * Retorna uma resposta de erro padronizada
     * @param string $message Mensagem de erro
     * @param int $statusCode Código de status HTTP (opcional, usado para logging)
     * @return array Resposta formatada
     */
    private static function error($message = 'Erro interno do servidor', $statusCode = 500) {
        return [
            'success' => false,
            'sucesso' => false,
            'error' => $message,
            'erro' => $message,
            'message' => $message,
            'status_code' => $statusCode
        ];
    }
    
    /**
     * Lista atividades disponíveis com base no tipo especificado ou todas se não especificado
     * @param array $usuarioLogado Dados do usuário autenticado
     * @param string|null $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa) ou null para todas
     * @param int $pagina Página atual
     * @param int $limite Limite de registros por página
     * @param string $ordenacao Campo para ordenação
     * @param string $direcao Direção da ordenação (ASC/DESC)
     * @param string $busca Termo de busca
     * @return array Resposta da API
     */
    public static function listarAtividades($usuarioLogado, $tipo = null, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '') {
        try {
            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $direcao = strtoupper($direcao);
            $busca = trim($busca);
            
            // Validar direção de ordenação
            if (!in_array($direcao, ['ASC', 'DESC'])) {
                $direcao = 'ASC';
            }
            
            // Validar campos de ordenação permitidos
            $camposPermitidos = ['nome', 'categoria', 'carga_horaria_maxima'];
            if (!in_array($ordenacao, $camposPermitidos)) {
                $ordenacao = 'nome';
            }
            
            // Validar tipo de atividade se especificado
            if ($tipo && !ListarAtividadesDisponiveisModel::tipoAtividadeValido($tipo)) {
                error_log("ListarAtividadesDisponiveisController::listarAtividades - Tipo de atividade inválido: $tipo");
                return self::error("Tipo de atividade inválido: $tipo", 400);
            }
            
            // Obter matrícula do usuário (obrigatória apenas para alunos)
            $matricula = isset($usuarioLogado['matricula']) ? $usuarioLogado['matricula'] : null;
            if ($usuarioLogado['tipo'] === 'aluno' && !$matricula) {
                error_log("ListarAtividadesDisponiveisController::listarAtividades - Matrícula não encontrada para aluno logado");
                return self::error("Matrícula do usuário não encontrada", 400);
            }
            
            error_log("ListarAtividadesDisponiveisController::listarAtividades - Usuário: " . json_encode($usuarioLogado));
            error_log("ListarAtividadesDisponiveisController::listarAtividades - Matrícula: $matricula");
            error_log("ListarAtividadesDisponiveisController::listarAtividades - Tipo: " . ($tipo ?: 'TODOS'));
            error_log("ListarAtividadesDisponiveisController::listarAtividades - Parâmetros: página=$pagina, limite=$limite, ordenação=$ordenacao $direcao, busca='$busca'");
            
            // Se tipo específico foi solicitado
            if ($tipo) {
                // Caso especial: aluno BCC (não SI) com matrícula entre 2017-2022 deve ver extracurriculares da resolução ID = 1
                $isAluno = strtolower((string)$usuarioLogado['tipo']) === 'aluno';
                $anoMatricula = null;
                if ($isAluno && $matricula) {
                    $anoMatricula = (int)substr((string)$matricula, 0, 4);
                }
                $periodo2017a2022 = $anoMatricula && $anoMatricula >= 2017 && $anoMatricula <= 2022;
                $periodo2023mais = $anoMatricula && $anoMatricula >= 2023;

                if ($isAluno && $periodo2017a2022) {
                    // Aplicar filtro pela resolução correta do período 2017-2022 conforme o tipo
                    $map = ListarAtividadesDisponiveisModel::RESOLUCOES_POR_TIPO;
                    $resolucaoId = isset($map[$tipo]['2017-2022']) ? $map[$tipo]['2017-2022'] : 1; // fallback seguro
                    error_log("ListarAtividadesDisponiveisController::listarAtividades - Aplicando filtro por resolucao_id={$resolucaoId} para aluno 2017-2022 no tipo: $tipo");
                    $resultado = ListarAtividadesDisponiveisModel::listarPorTipoComResolucaoId(
                        $tipo,
                        $resolucaoId,
                        $pagina,
                        $limite,
                        $ordenacao,
                        $direcao,
                        $busca
                    );
                    return self::success($resultado, "Atividades de $tipo listadas com sucesso (resolução {$resolucaoId})");
                }

                // Caso especial: aluno BCC com matrícula 2023+ deve ver extracurriculares da resolução_id = 2 (rta.id = 8)
                if ($isAluno && $periodo2023mais && $tipo === 'extracurriculares') {
                    $map = ListarAtividadesDisponiveisModel::RESOLUCOES_POR_TIPO;
                    $resolucaoId = isset($map['extracurriculares']['2023+']) ? $map['extracurriculares']['2023+'] : 8; // rta.id para (resolução 2, tipo 3)
                    error_log("ListarAtividadesDisponiveisController::listarAtividades - Aplicando filtro por resolucao_id={$resolucaoId} para aluno 2023+ no tipo: $tipo");
                    $resultado = ListarAtividadesDisponiveisModel::listarPorTipoComResolucaoId(
                        $tipo,
                        $resolucaoId,
                        $pagina,
                        $limite,
                        $ordenacao,
                        $direcao,
                        $busca
                    );
                    return self::success($resultado, "Atividades de $tipo listadas com sucesso (resolução {$resolucaoId})");
                }

                // Caso especial: aluno BCC com matrícula 2023+ deve ver ensino da resolução_id = 2 (rta.id = 6)
                if ($isAluno && $periodo2023mais && $tipo === 'ensino') {
                    $map = ListarAtividadesDisponiveisModel::RESOLUCOES_POR_TIPO;
                    $resolucaoId = isset($map['ensino']['2023+']) ? $map['ensino']['2023+'] : 6; // rta.id para (resolução 2, tipo 1)
                    error_log("ListarAtividadesDisponiveisController::listarAtividades - Aplicando filtro por resolucao_id={$resolucaoId} para aluno 2023+ no tipo: $tipo");
                    $resultado = ListarAtividadesDisponiveisModel::listarPorTipoComResolucaoId(
                        $tipo,
                        $resolucaoId,
                        $pagina,
                        $limite,
                        $ordenacao,
                        $direcao,
                        $busca
                    );
                    return self::success($resultado, "Atividades de $tipo listadas com sucesso (resolução {$resolucaoId})");
                }

                // Caso especial: aluno BCC com matrícula 2023+ deve ver estágio da resolução_id = 2 (rta.id = 9)
                if ($isAluno && $periodo2023mais && $tipo === 'estagio') {
                    $map = ListarAtividadesDisponiveisModel::RESOLUCOES_POR_TIPO;
                    $resolucaoId = isset($map['estagio']['2023+']) ? $map['estagio']['2023+'] : 9; // rta.id para (resolução 2, tipo 4)
                    error_log("ListarAtividadesDisponiveisController::listarAtividades - Aplicando filtro por resolucao_id={$resolucaoId} para aluno 2023+ no tipo: $tipo");
                    $resultado = ListarAtividadesDisponiveisModel::listarPorTipoComResolucaoId(
                        $tipo,
                        $resolucaoId,
                        $pagina,
                        $limite,
                        $ordenacao,
                        $direcao,
                        $busca
                    );
                    return self::success($resultado, "Atividades de $tipo listadas com sucesso (resolução {$resolucaoId})");
                }

                // Caso especial: aluno BCC com matrícula 2023+ deve ver pesquisa da resolução_id = 2 (rta.id = 7)
                if ($isAluno && $periodo2023mais && $tipo === 'pesquisa') {
                    $map = ListarAtividadesDisponiveisModel::RESOLUCOES_POR_TIPO;
                    $resolucaoId = isset($map['pesquisa']['2023+']) ? $map['pesquisa']['2023+'] : 7; // rta.id para (resolução 2, tipo 2)
                    error_log("ListarAtividadesDisponiveisController::listarAtividades - Aplicando filtro por resolucao_id={$resolucaoId} para aluno 2023+ no tipo: $tipo");
                    $resultado = ListarAtividadesDisponiveisModel::listarPorTipoComResolucaoId(
                        $tipo,
                        $resolucaoId,
                        $pagina,
                        $limite,
                        $ordenacao,
                        $direcao,
                        $busca
                    );
                    return self::success($resultado, "Atividades de $tipo listadas com sucesso (resolução {$resolucaoId})");
                }

                // Padrão: listar por tipo sem filtrar por curso/resolução
                error_log("ListarAtividadesDisponiveisController::listarAtividades - Listando por tipo sem filtro: $tipo");
                $resultado = ListarAtividadesDisponiveisModel::listarPorTipoSemFiltro(
                    $tipo,
                    $pagina,
                    $limite,
                    $ordenacao,
                    $direcao,
                    $busca
                );
                return self::success($resultado, "Atividades de $tipo listadas com sucesso (sem filtro)");
                
            } else {
                // Listar todas as atividades sem filtrar por resolução/curso (BCC/BSI)
                error_log("ListarAtividadesDisponiveisController::listarAtividades - Listando todas as atividades sem filtro de curso ou resolução");
                $resultado = ListarAtividadesDisponiveisModel::listarTodasSemFiltro(
                    $pagina,
                    $limite,
                    $ordenacao,
                    $direcao,
                    $busca
                );
                return self::success($resultado, "Todas as atividades disponíveis listadas com sucesso (sem filtro)");
            }
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisController::listarAtividades: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return self::error("Erro interno do servidor: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Lista todas as atividades disponíveis (compatibilidade com controllers antigos)
     * @param array $usuarioLogado Dados do usuário autenticado
     * @param int $pagina Página atual
     * @param int $limite Limite de registros por página
     * @param string $ordenacao Campo para ordenação
     * @param string $direcao Direção da ordenação (ASC/DESC)
     * @param string $busca Termo de busca
     * @return array Resposta da API
     */
    public static function listarTodas($usuarioLogado, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '') {
        return self::listarAtividades($usuarioLogado, null, $pagina, $limite, $ordenacao, $direcao, $busca);
    }
    
    /**
     * Lista atividades de ensino (compatibilidade com AtividadeEnsinoController)
     * @param array $usuarioLogado Dados do usuário autenticado
     * @param int $pagina Página atual
     * @param int $limite Limite de registros por página
     * @param string $ordenacao Campo para ordenação
     * @param string $direcao Direção da ordenação (ASC/DESC)
     * @param string $busca Termo de busca
     * @return array Resposta da API
     */
    public static function listarAtividadesEnsino($usuarioLogado, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '') {
        return self::listarAtividades($usuarioLogado, 'ensino', $pagina, $limite, $ordenacao, $direcao, $busca);
    }
    
    /**
     * Lista atividades de estágio (compatibilidade com AtividadeEstagioController)
     * @param array $dados Dados da requisição
     * @return array Resposta da API
     */
    public static function listarPorResolucao($dados) {
        try {
            // Extrair parâmetros dos dados
            $usuarioLogado = isset($dados['usuario_logado']) ? $dados['usuario_logado'] : [];
            $pagina = isset($dados['pagina']) ? (int)$dados['pagina'] : 1;
            $limite = isset($dados['limite']) ? (int)$dados['limite'] : 20;
            $ordenacao = isset($dados['ordenacao']) ? $dados['ordenacao'] : 'nome';
            $direcao = isset($dados['direcao']) ? $dados['direcao'] : 'ASC';
            $busca = isset($dados['busca']) ? $dados['busca'] : '';
            
            return self::listarAtividades($usuarioLogado, 'estagio', $pagina, $limite, $ordenacao, $direcao, $busca);
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisController::listarPorResolucao: " . $e->getMessage());
            return self::error("Erro interno do servidor: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Lista atividades extracurriculares (compatibilidade com AtividadeExtracurricularController)
     * @param array $usuarioLogado Dados do usuário autenticado
     * @param int $pagina Página atual
     * @param int $limite Limite de registros por página
     * @param string $ordenacao Campo para ordenação
     * @param string $direcao Direção da ordenação (ASC/DESC)
     * @param string $busca Termo de busca
     * @return array Resposta da API
     */
    public static function listarAtividadesExtracurriculares($usuarioLogado, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '') {
        return self::listarAtividades($usuarioLogado, 'extracurriculares', $pagina, $limite, $ordenacao, $direcao, $busca);
    }
    
    /**
     * Lista atividades de pesquisa (compatibilidade com AtividadePesquisaController)
     * @param array $usuarioLogado Dados do usuário autenticado
     * @param int $pagina Página atual
     * @param int $limite Limite de registros por página
     * @param string $ordenacao Campo para ordenação
     * @param string $direcao Direção da ordenação (ASC/DESC)
     * @param string $busca Termo de busca
     * @return array Resposta da API
     */
    public static function listarAtividadesPesquisa($usuarioLogado, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '') {
        return self::listarAtividades($usuarioLogado, 'pesquisa', $pagina, $limite, $ordenacao, $direcao, $busca);
    }
    
    /**
     * Busca uma atividade específica por ID
     * @param int $id ID da atividade
     * @return array Resposta da API
     */
    public static function buscarPorId($id) {
        try {
            if (!$id || !is_numeric($id)) {
                return self::error("ID da atividade é obrigatório e deve ser numérico", 400);
            }
            
            $atividade = ListarAtividadesDisponiveisModel::buscarPorId((int)$id);
            
            if (!$atividade) {
                return self::error("Atividade não encontrada", 404);
            }
            
            return self::success($atividade, "Atividade encontrada com sucesso");
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisController::buscarPorId: " . $e->getMessage());
            return self::error("Erro interno do servidor: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtém informações sobre os tipos de atividades disponíveis
     * @return array Resposta da API
     */
    public static function obterTiposAtividades() {
        try {
            $tipos = ListarAtividadesDisponiveisModel::obterTiposAtividades();
            
            return self::success([
                'tipos' => $tipos,
                'descricoes' => [
                    'ensino' => 'Atividades de Ensino',
                    'estagio' => 'Atividades de Estágio',
                    'extracurriculares' => 'Atividades Extracurriculares',
                    'pesquisa' => 'Atividades de Pesquisa'
                ]
            ], "Tipos de atividades obtidos com sucesso");
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisController::obterTiposAtividades: " . $e->getMessage());
            return self::error("Erro interno do servidor: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtém informações sobre as resoluções do usuário
     * @param array $usuarioLogado Dados do usuário autenticado
     * @return array Resposta da API
     */
    public static function obterInfoResolucoes($usuarioLogado) {
        try {
            $matricula = isset($usuarioLogado['matricula']) ? $usuarioLogado['matricula'] : null;
            if (!$matricula) {
                return self::error("Matrícula do usuário não encontrada", 400);
            }
            
            $info = ListarAtividadesDisponiveisModel::obterInfoResolucoes($matricula);
            
            if (!$info) {
                return self::error("Não foi possível obter informações das resoluções", 400);
            }
            
            return self::success($info, "Informações das resoluções obtidas com sucesso");
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisController::obterInfoResolucoes: " . $e->getMessage());
            return self::error("Erro interno do servidor: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Lista atividades enviadas pelo aluno logado
     * @param array $usuarioLogado Dados do usuário autenticado
     * @param int $pagina Página atual
     * @param int $limite Limite de registros por página
     * @param string $ordenacao Campo para ordenação
     * @param string $direcao Direção da ordenação (ASC/DESC)
     * @param string $busca Termo de busca
     * @return array Resposta da API
     */
    public static function listarAtividadesEnviadas($usuarioLogado, $pagina = 1, $limite = 20, $ordenacao = 'id', $direcao = 'DESC', $busca = '') {
        try {
            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $direcao = strtoupper($direcao);
            $busca = trim($busca);
            
            // Validar direção de ordenação
            if (!in_array($direcao, ['ASC', 'DESC'])) {
                $direcao = 'DESC';
            }
            
            // Validar campos de ordenação permitidos
            $camposPermitidos = ['id', 'titulo', 'data_submissao', 'status', 'ch_solicitada', 'ch_atribuida', 'categoria'];
            if (!in_array($ordenacao, $camposPermitidos)) {
                $ordenacao = 'id';
            }
            
            error_log("ListarAtividadesDisponiveisController::listarAtividadesEnviadas - Usuário: " . json_encode($usuarioLogado));
            // Suporte a $usuarioLogado como array ou objeto para evitar erros de offset
            $tipoUsuario = null;
            if (is_array($usuarioLogado) && isset($usuarioLogado['tipo'])) {
                $tipoUsuario = strtolower((string)$usuarioLogado['tipo']);
            } elseif (is_object($usuarioLogado) && isset($usuarioLogado->tipo)) {
                $tipoUsuario = strtolower((string)$usuarioLogado->tipo);
            }
            error_log("ListarAtividadesDisponiveisController::listarAtividadesEnviadas - Tipo de usuário: " . ($tipoUsuario ?? 'desconhecido'));
            error_log("ListarAtividadesDisponiveisController::listarAtividadesEnviadas - Parâmetros: página=$pagina, limite=$limite, ordenação=$ordenacao $direcao, busca='$busca'");
            
            // Verificar tipo de usuário e chamar função apropriada
            if ($tipoUsuario === 'coordenador') {
                // Coordenadores veem apenas atividades de alunos do seu curso
                error_log("ListarAtividadesDisponiveisController::listarAtividadesEnviadas - Coordenador acessando: listando atividades do seu curso");
                // Obter ID com suporte a array/objeto
                $coordenadorId = null;
                if (is_array($usuarioLogado) && isset($usuarioLogado['id'])) {
                    $coordenadorId = (int)$usuarioLogado['id'];
                } elseif (is_object($usuarioLogado) && isset($usuarioLogado->id)) {
                    $coordenadorId = (int)$usuarioLogado->id;
                }
                if (!$coordenadorId) {
                    error_log("ListarAtividadesDisponiveisController::listarAtividadesEnviadas - ID do coordenador não encontrado no usuário logado");
                    return self::error("ID do coordenador não encontrado", 400);
                }
                $resultado = ListarAtividadesDisponiveisModel::listarTodasAtividadesEnviadas(
                    $pagina, 
                    $limite, 
                    $ordenacao, 
                    $direcao, 
                    $busca,
                    $coordenadorId
                );
            } else {
                // Alunos veem apenas suas próprias atividades
                // Obter ID com suporte a array/objeto
                $aluno_id = null;
                if (is_array($usuarioLogado) && isset($usuarioLogado['id'])) {
                    $aluno_id = $usuarioLogado['id'];
                } elseif (is_object($usuarioLogado) && isset($usuarioLogado->id)) {
                    $aluno_id = $usuarioLogado->id;
                }
                if (!$aluno_id) {
                    error_log("ListarAtividadesDisponiveisController::listarAtividadesEnviadas - ID do aluno não encontrado no usuário logado");
                    return self::error("ID do aluno não encontrado", 400);
                }
                
                error_log("ListarAtividadesDisponiveisController::listarAtividadesEnviadas - Aluno acessando: ID $aluno_id");
                $resultado = ListarAtividadesDisponiveisModel::listarAtividadesEnviadasPorAluno(
                    $aluno_id, 
                    $pagina, 
                    $limite, 
                    $ordenacao, 
                    $direcao, 
                    $busca
                );
            }
            
            return self::success($resultado, "Atividades enviadas listadas com sucesso");
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisController::listarAtividadesEnviadas: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return self::error("Erro interno do servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Determina a resolução baseada na matrícula do aluno (compatibilidade)
     * @param string $matricula Matrícula do aluno
     * @param string|null $tipo Tipo de atividade (opcional)
     * @return int|array|null ID da resolução ou array de resoluções
     */
    public static function determinarResolucaoPorMatricula($matricula, $tipo = null) {
        return ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula($matricula, $tipo);
    }
}