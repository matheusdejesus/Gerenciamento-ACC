<?php

namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;
use Exception;

class ListarAtividadesDisponiveisModel {
    
    // Mapeamento dos tipos de atividades para nomes na base de dados
    const TIPOS_ATIVIDADES = [
        'ensino' => 'Ensino',
        'estagio' => 'Estágio',
        'extracurriculares' => 'Atividades extracurriculares',
        'pesquisa' => 'Pesquisa',
        'acao_social' => 'Atividades sociais e comunitárias'
    ];
    
    // Mapeamento das resoluções por tipo de atividade
    const RESOLUCOES_POR_TIPO = [
        'ensino' => [
            '2017-2022' => 1,
            '2023+' => 6
        ],
        'estagio' => [
            '2017-2022' => 4,
            '2023+' => 9
        ],
        'extracurriculares' => [
            '2017-2022' => 3,
            '2023+' => 8
        ],
        'pesquisa' => [
            '2017-2022' => 2,
            '2023+' => 7
        ],
        'acao_social' => [
            '2017-2022' => 5,
            '2023+' => 6
        ]
    ];
    
    /**
     * Lista atividades por tipo e resolução com paginação e filtros
     * @param string $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa)
     * @param int $resolucaoTipoAtividadeId ID da resolução
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'nome')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'ASC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarAtividades($tipo, $resolucaoTipoAtividadeId, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '') {
        // Este método é um alias para listarPorTipoEResolucao para compatibilidade
        return self::listarPorTipoEResolucao($tipo, $resolucaoTipoAtividadeId, $pagina, $limite, $ordenacao, $direcao, $busca);
    }

    /**
     * Lista atividades por tipo e resolução com paginação e filtros
     * @param string $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa)
     * @param int $resolucaoTipoAtividadeId ID da resolução
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'nome')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'ASC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarPorTipoEResolucao($tipo, $resolucaoTipoAtividadeId, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '') {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Validar tipo de atividade
            if (!isset(self::TIPOS_ATIVIDADES[$tipo])) {
                throw new Exception("Tipo de atividade inválido: $tipo");
            }
            
            $nomeAtividade = self::TIPOS_ATIVIDADES[$tipo];
            
            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;
            
            // Mapear campos de ordenação
            $camposOrdenacao = [
                'nome' => 'ac.titulo',
                'categoria' => 'ta.nome',
                'carga_horaria_maxima' => 'apr.carga_horaria_maxima_por_atividade'
            ];
            
            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ac.titulo';
            $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';
            
            // Construir condição de busca
            $condicaoBusca = '';
            $parametros = [$resolucaoTipoAtividadeId];
            $tipos = 'i';
            
            if (!empty($busca)) {
                $condicaoBusca = " AND (ac.titulo LIKE ? OR ac.descricao LIKE ? OR ta.nome LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tipos .= 'sss';
            }
            
            // Query para contar total de registros
            $sqlCount = "SELECT COUNT(*) as total
                        FROM atividades_por_resolucao apr
                        JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        WHERE apr.resolucao_tipo_atividade_id = ? 
                        AND ta.nome = '$nomeAtividade' $condicaoBusca";
            
            error_log("SQL Count Query ($tipo): " . $sqlCount);
            error_log("Parameters: " . json_encode($parametros));
            
            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }
            
            $stmtCount->bind_param($tipos, ...$parametros);
            
            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }
            
            $resultCount = $stmtCount->get_result();
            $total = $resultCount->fetch_assoc()['total'];
            $stmtCount->close();
            
            // Query principal com paginação
            $sql = "SELECT 
                        apr.id as atividades_por_resolucao_id,
                        ac.id as atividade_complementar_id,
                        ac.titulo as nome,
                        ac.descricao,
                        ac.observacoes,
                        ta.nome as categoria,
                        apr.carga_horaria_maxima_por_atividade as carga_horaria_maxima,
                        ta.nome as tipo,
                        apr.carga_horaria_maxima_por_atividade as horas_max,
                        '$tipo' as tipo_atividade
                    FROM atividades_por_resolucao apr
                    JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    WHERE apr.resolucao_tipo_atividade_id = ? 
                    AND ta.nome = '$nomeAtividade' $condicaoBusca
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";
                    
            error_log("SQL Main Query ($tipo): " . $sql);
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }
            
            // Adicionar parâmetros de paginação
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= 'ii';
            
            $stmt->bind_param($tipos, ...$parametros);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'tipo' => $row['tipo'],
                    'horas_max' => (int)$row['horas_max'],
                    'tipo_atividade' => $row['tipo_atividade']
                ];
            }
            
            $stmt->close();
            
            // Calcular metadados de paginação
            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;
            
            error_log("ListarAtividadesDisponiveisModel::listarPorTipoEResolucao - Encontradas " . count($atividades) . " atividades de $tipo (página $pagina de $totalPaginas) para resolução " . $resolucaoTipoAtividadeId);
            
            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarPorTipoEResolucao: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Lista todas as atividades disponíveis (todos os tipos) por resolução
     * @param int $resolucaoTipoAtividadeId ID da resolução
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'nome')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'ASC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarTodasPorResolucao($resolucaoTipoAtividadeId, $pagina = 1, $limite = 20, $ordenacao = 'nome', $direcao = 'ASC', $busca = '') {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;
            
            // Mapear campos de ordenação
            $camposOrdenacao = [
                'nome' => 'ac.titulo',
                'categoria' => 'ta.nome',
                'carga_horaria_maxima' => 'apr.carga_horaria_maxima_por_atividade'
            ];
            
            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ac.titulo';
            $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';
            
            // Construir condição de busca
            $condicaoBusca = '';
            $parametros = [$resolucaoTipoAtividadeId];
            $tipos = 'i';
            
            if (!empty($busca)) {
                $condicaoBusca = " AND (ac.titulo LIKE ? OR ac.descricao LIKE ? OR ta.nome LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tipos .= 'sss';
            }
            
            // Lista dos tipos de atividades válidos
            $tiposValidos = "'" . implode("', '", array_values(self::TIPOS_ATIVIDADES)) . "'";
            
            // Query para contar total de registros
            $sqlCount = "SELECT COUNT(*) as total
                        FROM atividades_por_resolucao apr
                        JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        WHERE apr.resolucao_tipo_atividade_id = ? 
                        AND ta.nome IN ($tiposValidos) $condicaoBusca";
            
            error_log("SQL Count Query (Todas): " . $sqlCount);
            error_log("Parameters: " . json_encode($parametros));
            
            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }
            
            $stmtCount->bind_param($tipos, ...$parametros);
            
            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }
            
            $resultCount = $stmtCount->get_result();
            $total = $resultCount->fetch_assoc()['total'];
            $stmtCount->close();
            
            // Query principal com paginação
            $sql = "SELECT 
                        apr.id as atividades_por_resolucao_id,
                        ac.id as atividade_complementar_id,
                        ac.titulo as nome,
                        ac.descricao,
                        ac.observacoes,
                        ta.nome as categoria,
                        apr.carga_horaria_maxima_por_atividade as carga_horaria_maxima,
                        ta.nome as tipo,
                        apr.carga_horaria_maxima_por_atividade as horas_max,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividades_por_resolucao apr
                    JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    WHERE apr.resolucao_tipo_atividade_id = ? 
                    AND ta.nome IN ($tiposValidos) $condicaoBusca
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";
                    
            error_log("SQL Main Query (Todas): " . $sql);
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }
            
            // Adicionar parâmetros de paginação
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= 'ii';
            
            $stmt->bind_param($tipos, ...$parametros);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'tipo' => $row['tipo'],
                    'horas_max' => (int)$row['horas_max'],
                    'tipo_atividade' => $row['tipo_atividade']
                ];
            }
            
            $stmt->close();
            
            // Calcular metadados de paginação
            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;
            
            error_log("ListarAtividadesDisponiveisModel::listarTodasPorResolucao - Encontradas " . count($atividades) . " atividades (página $pagina de $totalPaginas) para resolução " . $resolucaoTipoAtividadeId);
            
            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarTodasPorResolucao: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca uma atividade específica por ID
     * @param int $id ID da atividade
     * @return array|null Dados da atividade ou null se não encontrada
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Lista dos tipos de atividades válidos
            $tiposValidos = "'" . implode("', '", array_values(self::TIPOS_ATIVIDADES)) . "'";
            
            $sql = "SELECT 
                        apr.id as atividades_por_resolucao_id,
                        ac.id as atividade_complementar_id,
                        ac.titulo as nome,
                        ac.descricao,
                        ac.observacoes,
                        ta.nome as categoria,
                        apr.carga_horaria_maxima_por_atividade as carga_horaria_maxima,
                        apr.resolucao_tipo_atividade_id,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividades_complementares ac
                    JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    JOIN atividades_por_resolucao apr ON apr.atividades_complementares_id = ac.id
                    WHERE ac.id = ? AND ta.nome IN ($tiposValidos)";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $atividade = null;
            
            if ($row = $result->fetch_assoc()) {
                $atividade = [
                    'id' => (int)$row['atividades_por_resolucao_id'],
                    'atividade_complementar_id' => (int)$row['atividade_complementar_id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'observacoes' => $row['observacoes'],
                    'categoria' => $row['categoria'],
                    'carga_horaria_maxima' => (int)$row['carga_horaria_maxima'],
                    'resolucao_tipo_atividade_id' => (int)$row['resolucao_tipo_atividade_id'],
                    'tipo_atividade' => $row['tipo_atividade']
                ];
            }
            
            $stmt->close();
            
            error_log("ListarAtividadesDisponiveisModel::buscarPorId - Atividade ID $id: " . ($atividade ? 'encontrada' : 'não encontrada'));
            
            return $atividade;
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Determina a resolução baseada na matrícula do aluno e tipo de atividade
     * @param string $matricula Matrícula do aluno
     * @param string $tipo Tipo de atividade (ensino, estagio, extracurriculares, pesquisa)
     * @return int|null ID da resolução ou null se não encontrada
     */
    public static function determinarResolucaoPorMatricula($matricula, $tipo = null) {
        try {
            // Extrair ano da matrícula (primeiros 4 dígitos)
            $ano = (int)substr($matricula, 0, 4);
            
            error_log("ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula - Matrícula: $matricula, Ano extraído: $ano, Tipo: $tipo");
            
            // Determinar período baseado no ano
            $periodo = ($ano >= 2017 && $ano <= 2022) ? '2017-2022' : '2023+';
            
            // Se tipo específico foi fornecido, retornar resolução específica
            if ($tipo && isset(self::RESOLUCOES_POR_TIPO[$tipo][$periodo])) {
                $resolucaoId = self::RESOLUCOES_POR_TIPO[$tipo][$periodo];
                error_log("ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula - Resolução específica para $tipo ($periodo): $resolucaoId");
                return $resolucaoId;
            }
            
            // Se não foi especificado tipo, retornar array com todas as resoluções do período
            $resolucoes = [];
            foreach (self::RESOLUCOES_POR_TIPO as $tipoAtiv => $periodos) {
                if (isset($periodos[$periodo])) {
                    $resolucoes[$tipoAtiv] = $periodos[$periodo];
                }
            }
            
            error_log("ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula - Resoluções para período $periodo: " . json_encode($resolucoes));
            
            return $resolucoes;
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::determinarResolucaoPorMatricula: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Valida se um tipo de atividade é válido
     * @param string $tipo Tipo de atividade
     * @return bool True se válido, false caso contrário
     */
    public static function tipoAtividadeValido($tipo) {
        return isset(self::TIPOS_ATIVIDADES[$tipo]);
    }
    
    /**
     * Obtém todos os tipos de atividades disponíveis
     * @return array Array com os tipos de atividades
     */
    public static function obterTiposAtividades() {
        return array_keys(self::TIPOS_ATIVIDADES);
    }
    
    /**
     * Lista atividades enviadas por um aluno específico com paginação e filtros
     * @param int $aluno_id ID do aluno
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'id')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'DESC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarAtividadesEnviadasPorAluno($aluno_id, $pagina = 1, $limite = 20, $ordenacao = 'id', $direcao = 'DESC', $busca = '') {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;
            
            // Mapear campos de ordenação
            $camposOrdenacao = [
                'id' => 'ae.id',
                'titulo' => 'ae.titulo',
                'data_avaliacao' => 'ae.data_avaliacao',
                'status' => 'ae.status',
                'ch_solicitada' => 'ae.ch_solicitada',
                'ch_atribuida' => 'ae.ch_atribuida',
                'categoria' => 'ta.nome'
            ];
            
            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ae.id';
            $direcao = strtoupper($direcao) === 'ASC' ? 'ASC' : 'DESC';
            
            // Construir condição de busca
            $condicaoBusca = '';
            $parametros = [$aluno_id];
            $tipos = 'i';
            
            if (!empty($busca)) {
                $condicaoBusca = " AND (ae.titulo LIKE ? OR ae.descricao LIKE ? OR ac.titulo LIKE ? OR ta.nome LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $parametros[] = $termoBusca;
                $tipos .= 'ssss';
            }
            
            // Query para contar total de registros
            $sqlCount = "SELECT COUNT(*) as total
                        FROM atividade_enviada ae
                        LEFT JOIN atividades_por_resolucao apr ON ae.atividades_por_resolucao_id = apr.id
                        LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        WHERE ae.aluno_id = ? $condicaoBusca";
            
            error_log("SQL Count Query (Atividades Enviadas): " . $sqlCount);
            error_log("Parameters: " . json_encode($parametros));
            
            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }
            
            $stmtCount->bind_param($tipos, ...$parametros);
            
            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }
            
            $resultCount = $stmtCount->get_result();
            $total = $resultCount->fetch_assoc()['total'];
            $stmtCount->close();
            
            // Query principal com paginação
            $sql = "SELECT 
                        ae.id,
                        ae.titulo,
                        ae.descricao,
                        ae.ch_solicitada,
                        ae.ch_atribuida,
                        ae.status,
                        ae.data_avaliacao,
                        ae.caminho_declaracao,
                        ac.titulo as atividade_titulo,
                        ta.nome as categoria_nome,
                        ae.atividades_por_resolucao_id,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            WHEN ta.nome = 'Atividades sociais e comunitárias' THEN 'acao_social'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividade_enviada ae
                    LEFT JOIN atividades_por_resolucao apr ON ae.atividades_por_resolucao_id = apr.id
                    LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    WHERE ae.aluno_id = ? $condicaoBusca
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";
                    
            error_log("SQL Main Query (Atividades Enviadas): " . $sql);
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }
            
            // Adicionar parâmetros de paginação
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= 'ii';
            
            $stmt->bind_param($tipos, ...$parametros);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'ch_solicitada' => (int)$row['ch_solicitada'],
                    'ch_atribuida' => $row['ch_atribuida'] ? (int)$row['ch_atribuida'] : null,
                    'status' => $row['status'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'caminho_declaracao' => $row['caminho_declaracao'],
                    'atividade_titulo' => $row['atividade_titulo'],
                    'categoria_nome' => $row['categoria_nome'],
                    'atividades_por_resolucao_id' => (int)$row['atividades_por_resolucao_id'],
                    'tipo_atividade' => $row['tipo_atividade']
                ];
            }
            
            $stmt->close();
            
            // Calcular metadados de paginação
            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;
            
            error_log("ListarAtividadesDisponiveisModel::listarAtividadesEnviadasPorAluno - Encontradas " . count($atividades) . " atividades (página $pagina de $totalPaginas) para aluno " . $aluno_id);
            
            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarAtividadesEnviadasPorAluno: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista todas as atividades enviadas por todos os alunos (para coordenadores)
     * @param int $pagina Página atual (padrão: 1)
     * @param int $limite Limite de registros por página (padrão: 20)
     * @param string $ordenacao Campo para ordenação (padrão: 'id')
     * @param string $direcao Direção da ordenação ASC/DESC (padrão: 'DESC')
     * @param string $busca Termo de busca (padrão: '')
     * @return array Array com as atividades encontradas e metadados de paginação
     */
    public static function listarTodasAtividadesEnviadas($pagina = 1, $limite = 20, $ordenacao = 'id', $direcao = 'DESC', $busca = '') {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Validar parâmetros
            $pagina = max(1, (int)$pagina);
            $limite = max(1, min(100, (int)$limite));
            $offset = ($pagina - 1) * $limite;
            
            // Mapear campos de ordenação
            $camposOrdenacao = [
                'id' => 'ae.id',
                'titulo' => 'ae.titulo',
                'data_avaliacao' => 'ae.data_avaliacao',
                'status' => 'ae.status',
                'ch_solicitada' => 'ae.ch_solicitada',
                'ch_atribuida' => 'ae.ch_atribuida',
                'categoria' => 'ta.nome',
                'aluno_nome' => 'u.nome',
                'data_submissao' => 'ae.id'
            ];
            
            $campoOrdenacao = isset($camposOrdenacao[$ordenacao]) ? $camposOrdenacao[$ordenacao] : 'ae.id';
            $direcao = strtoupper($direcao) === 'ASC' ? 'ASC' : 'DESC';
            
            // Construir condição de busca - SEMPRE filtrar apenas atividades pendentes
            $condicaoBusca = " WHERE ae.status = ?";
            $parametros = ['Aguardando avaliação'];
            $tipos = 's';
            
            if (!empty($busca)) {
                $condicaoBusca .= " AND (ae.titulo LIKE ? OR ae.descricao LIKE ? OR ac.titulo LIKE ? OR ta.nome LIKE ? OR u.nome LIKE ? OR a.matricula LIKE ?)";
                $termoBusca = '%' . $busca . '%';
                $parametros = array_merge($parametros, [$termoBusca, $termoBusca, $termoBusca, $termoBusca, $termoBusca, $termoBusca]);
                $tipos .= 'ssssss';
            }
            
            // Query para contar total de registros
            $sqlCount = "SELECT COUNT(*) as total
                        FROM atividade_enviada ae
                        LEFT JOIN atividades_por_resolucao apr ON ae.atividades_por_resolucao_id = apr.id
                        LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                        LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                        LEFT JOIN aluno a ON ae.aluno_id = a.usuario_id
                        LEFT JOIN usuario u ON a.usuario_id = u.id
                        LEFT JOIN curso c ON a.curso_id = c.id
                        $condicaoBusca";
            
            error_log("SQL Count Query (Todas Atividades Enviadas): " . $sqlCount);
            error_log("Parameters: " . json_encode($parametros));
            
            $stmtCount = $conn->prepare($sqlCount);
            if (!$stmtCount) {
                throw new Exception("Erro ao preparar consulta de contagem: " . $conn->error);
            }
            $stmtCount->bind_param($tipos, ...$parametros);
            
            if (!$stmtCount->execute()) {
                throw new Exception("Erro ao executar consulta de contagem: " . $stmtCount->error);
            }
            
            $resultCount = $stmtCount->get_result();
            $total = $resultCount->fetch_assoc()['total'];
            $stmtCount->close();
            
            // Query principal com paginação
            $sql = "SELECT 
                        ae.id,
                        ae.titulo,
                        ae.descricao,
                        ae.ch_solicitada,
                        ae.ch_atribuida,
                        ae.status,
                        ae.data_avaliacao,
                        ae.caminho_declaracao,
                        ac.titulo as atividade_titulo,
                        ta.nome as categoria_nome,
                        ae.atividades_por_resolucao_id,
                        u.nome as aluno_nome,
                        a.matricula as aluno_matricula,
                        c.nome as curso_nome,
                        ae.data_envio as data_submissao,
                        CASE 
                            WHEN ta.nome = 'Ensino' THEN 'ensino'
                            WHEN ta.nome = 'Estágio' THEN 'estagio'
                            WHEN ta.nome = 'Atividades extracurriculares' THEN 'extracurriculares'
                            WHEN ta.nome = 'Pesquisa' THEN 'pesquisa'
                            WHEN ta.nome = 'Atividades sociais e comunitárias' THEN 'acao_social'
                            ELSE 'outros'
                        END as tipo_atividade
                    FROM atividade_enviada ae
                    LEFT JOIN atividades_por_resolucao apr ON ae.atividades_por_resolucao_id = apr.id
                    LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    LEFT JOIN aluno a ON ae.aluno_id = a.usuario_id
                    LEFT JOIN usuario u ON a.usuario_id = u.id
                    LEFT JOIN curso c ON a.curso_id = c.id
                    $condicaoBusca
                    ORDER BY $campoOrdenacao $direcao
                    LIMIT ? OFFSET ?";
            
            error_log("SQL Main Query (Todas Atividades Enviadas): " . $sql);
            
            // Adicionar parâmetros de paginação
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= 'ii';
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta principal: " . $conn->error);
            }
            
            $stmt->bind_param($tipos, ...$parametros);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta principal: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'ch_solicitada' => (int)$row['ch_solicitada'],
                    'ch_atribuida' => (int)$row['ch_atribuida'],
                    'status' => $row['status'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'data_submissao' => $row['data_submissao'],
                    'caminho_declaracao' => $row['caminho_declaracao'],
                    'atividade_titulo' => $row['atividade_titulo'],
                    'categoria_nome' => $row['categoria_nome'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'curso_nome' => $row['curso_nome'],
                    'atividades_por_resolucao_id' => (int)$row['atividades_por_resolucao_id'],
                    'tipo_atividade' => $row['tipo_atividade'],
                    'tipo' => $row['tipo_atividade']
                ];
            }
            
            $stmt->close();
            
            // Calcular metadados de paginação
            $totalPaginas = ceil($total / $limite);
            $temProxima = $pagina < $totalPaginas;
            $temAnterior = $pagina > 1;
            
            error_log("ListarAtividadesDisponiveisModel::listarTodasAtividadesEnviadas - Encontradas " . count($atividades) . " atividades (página $pagina de $totalPaginas)");
            
            return [
                'atividades' => $atividades,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'limite' => $limite,
                    'tem_proxima' => $temProxima,
                    'tem_anterior' => $temAnterior
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::listarTodasAtividadesEnviadas: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém informações sobre as resoluções por tipo
     * @param string $matricula Matrícula do aluno
     * @return array Informações sobre as resoluções
     */
    public static function obterInfoResolucoes($matricula) {
        try {
            $ano = (int)substr($matricula, 0, 4);
            $periodo = ($ano >= 2017 && $ano <= 2022) ? '2017-2022' : '2023+';
            
            $info = [
                'matricula' => $matricula,
                'ano_matricula' => $ano,
                'periodo' => $periodo,
                'resolucoes' => []
            ];
            
            foreach (self::RESOLUCOES_POR_TIPO as $tipo => $periodos) {
                if (isset($periodos[$periodo])) {
                    $info['resolucoes'][$tipo] = [
                        'id' => $periodos[$periodo],
                        'nome' => "Resolução $periodo",
                        'tipo_atividade' => $tipo
                    ];
                }
            }
            
            return $info;
            
        } catch (Exception $e) {
            error_log("Erro em ListarAtividadesDisponiveisModel::obterInfoResolucoes: " . $e->getMessage());
            return null;
        }
    }
}